mod narou_types;
mod narou_parser;
mod api_types;
mod api_endpoint;

#[cfg(feature = "full-text")]
mod full_text;

#[cfg(feature = "full-text")]
mod full_text_novel;

use crate::api_endpoint::*;

use std::{env, io};
use actix_files as fs;
use actix_files::NamedFile;
use actix_web::{web, App, HttpServer};
use actix_web::dev::{fn_service, ServiceRequest, ServiceResponse};
use actix_web::middleware::DefaultHeaders;
use tracing_subscriber::prelude::*;
use crate::narou_parser::get_narou_root;

fn main() -> io::Result<()> {
    let sentry_dsn = env::var("SENTRY_DSN").unwrap_or("".to_string());
    println!("SENTRY_DSN: {}", sentry_dsn);

    let sentry_debug = env::var("SENTRY_DEBUG").unwrap_or("false".to_string());
    let sentry_debug = sentry_debug == "true";
    println!("SENTRY_DEBUG: {}", sentry_debug);

    tracing_subscriber::Registry::default()
        .with(sentry::integrations::tracing::layer())
        .init();
    let _guard = sentry::init((
        sentry_dsn,
        sentry::ClientOptions {
            release: sentry::release_name!(),
            traces_sample_rate: 1.0,
            send_default_pii: false,
            debug: sentry_debug,
            max_request_body_size: sentry::MaxRequestBodySize::Always,
            ..Default::default()
        }
    ));

    println!("NAROU_ROOT: {}", get_narou_root());

    let args: Vec<String> = env::args().collect();
    if args.len() == 2 {
        if args[1] == "index:all" {
            println!("MODE: Index narou novels and stories");
            #[cfg(feature = "full-text")]
            actix_web::rt::System::new().block_on(async {
                let _ = crate::full_text::index_all().await;
                let _ = crate::full_text_novel::novel_index_all().await;
            });
        } else if args[1] == "index:novel" {
            println!("MODE: Index narou novels");
            #[cfg(feature = "full-text")]
            actix_web::rt::System::new().block_on(async {
                let _ = crate::full_text_novel::novel_index_all().await;
            });
        } else if args[1] == "index:story" {
            println!("MODE: Index narou stories");
            #[cfg(feature = "full-text")]
            actix_web::rt::System::new().block_on(async {
                let _ = crate::full_text::index_all().await;
            });
        } else {
            eprintln!("Command not found");
        }

        return Ok(());
    }

    unsafe {
        let res = git2::opts::set_verify_owner_validation(false);
        if res.is_err() {
            eprintln!("Failed to disable git owner validation");
        }
    }

    println!("Pre-opening stories index DB...");
    #[cfg(feature = "full-text")]
    let _ = crate::full_text::open_index();

    println!("Pre-opening novels index DB...");
    #[cfg(feature = "full-text")]
    let _ = crate::full_text_novel::open_novel_index();

    let bind_addr = env::var("APP_BIND").unwrap_or("[::]:3001".to_string());
    println!("APP_BIND: {}", bind_addr);

    actix_web::rt::System::new().block_on(async {
        HttpServer::new(|| {
            App::new()
                .wrap(
                    sentry::integrations::actix::Sentry::builder()
                        .capture_server_errors(true) // Capture server errors
                        .start_transaction(true) // Start a transaction (Sentry root span) for each request
                        .finish(),
                )
                .service(
                    web::scope("/api")
                        // Default is 404
                        .service(api_list)
                        .service(api_story)
                        .service(api_novel_revision)
                        .service(api_content)
                        .service(api_list_inspect)
                        .service(api_story_inspect)
                        .service(api_content_inspect)
                        .service(api_index_search_story)
                        .service(api_index_search_story_inspect)
                        .service(api_index_search_novel)
                        //.service(api_index_search_novel_inspect)
                )
                .service(
                    web::scope("/assets")
                        .default_service(
                            fs::Files::new("/", "./frontend/dist/assets")
                                .prefer_utf8(true)
                                .use_etag(true)
                                .use_last_modified(true)
                        )
                        .wrap(
                            DefaultHeaders::new()
                                .add(("Cache-Control", "public, max-age=31536000"))
                        )
                )
                .service(
                     fs::Files::new("/", "./frontend/dist")
                         .prefer_utf8(true)
                         .index_file("index.html")
                         .use_etag(true)
                         .use_last_modified(true)
                         .default_handler(fn_service(|req: ServiceRequest| async {
                             let (req, _) = req.into_parts();
                             // For SPA application
                             let file = NamedFile::open_async("./frontend/dist/index.html").await?;
                             let res = file.into_response(&req);
                             Ok(ServiceResponse::new(req, res))
                         }))
                )
        })
            .bind(bind_addr)?
            .run()
            .await
    })?;
    Ok(())
}
