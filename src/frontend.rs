use actix_web::{get, web, HttpResponse, HttpResponseBuilder, Responder};
use sailfish::{TemplateOnce, TemplateSimple};
use serde::Deserialize;
use crate::api_types::{ApiElement, ApiNovelInfo, ApiNovelList, ApiNovelRevision, ApiReaderInfo, ApiStories, ApiSubtitle, InspectNovel};
use crate::narou_parser::{get_db_novel_info_by_id, load_content, load_index, load_toc_by_id, load_toc_histories};
use crate::narou_types::{NovelInfo};
use crate::api_endpoint::{extract_api_list, ApiFullTextSearchQueryParams, ApiListQueryParams};
use crate::frontend_templates::*;

#[get("/")]
pub async fn frontend_index() -> impl Responder {
    HttpResponse::MovedPermanently()
        .insert_header(("Location", "/novels"))
        .body("See: <a href=\"/novels\">/novels</a>")
}

#[get("/novels")]
pub async fn frontend_list(query: web::Query<ApiListQueryParams>) -> impl Responder {
    let has_commit_id = query.commit_id.is_some();

    let mut page = query.p.unwrap_or(1);
    if page == 0 { page = 1; }
    let skip = 100 * (page - 1u64);

    let data = extract_api_list(query).await;

    let ctx = NovelsTemplate {
        html_title: "小説一覧".to_string(),
        total: data.len() as u64,
        page,
        novels: data.iter().skip(skip as usize).take(100).map(|v| FrontendNovelInfo {
            id: v.id,
            title: v.title.clone(),
            author: v.author.clone(),
            general_lastup: v.general_lastup.timestamp() as u64,
            sitename: v.sitename.clone(),
            toc_url: v.toc_url.clone(),
        }).collect(),
    };

    HttpResponse::Ok()
        .insert_header(("Cache-Control", if has_commit_id {"public, max-age=31536000"} else {"public, max-age=600"}))
        .insert_header(("Content-type", "text/html"))
        .body(ctx.render_once().unwrap())
}

#[derive(Deserialize)]
struct FrontendStoryQueryParams {
    commit_id: Option<String>,
    // Note: frontend not supported.
    order: Option<String>,
    p: Option<u64>,
}

#[get("/novels/{novelId}")]
pub async fn frontend_story(path: web::Path<(u64,)>, query: web::Query<FrontendStoryQueryParams>) -> impl Responder {
    let path = path.into_inner();
    let novel_id = path.0;
    let commit_id: Option<&str> = if query.commit_id.is_some() { Some(query.commit_id.as_ref().unwrap()) } else { None };
    let toc = load_toc_by_id(novel_id, None, None, commit_id).await;
    if toc.is_err() {
        eprintln!("Failed to load toc: {}", novel_id);
        return HttpResponse::InternalServerError().body("Failed to load toc");
    }
    let toc = toc.unwrap();

    let mut page = query.p.unwrap_or(1);
    if page == 0 { page = 1; }
    let skip = 100 * (page - 1u64);

    let ctx = StoriesTemplate {
        html_title: toc.title.clone(),
        novel_id,
        title: toc.title.clone(),
        author: toc.author.clone(),
        story: toc.story.clone(),
        subtitles: toc.subtitles.iter().skip(skip as usize).take(100).map(|v| ApiSubtitle {
            index: v.index.clone().parse::<u64>().unwrap(),
            chapter: v.chapter.clone(),
            subchapter: v.subchapter.clone(),
            subtitle: v.subtitle.clone(),
            subdate: v.subdate.clone(),
            subupdate: v.subupdate.clone(),
            reader_info: None,
            novel_info: None,
        }).collect(),
        total: toc.subtitles.len() as u64,
        page,
    };

    HttpResponse::Ok()
        .insert_header(("Cache-Control", if commit_id.is_some() {"public, max-age=31536000"} else {"public, max-age=600"}))
        .insert_header(("Content-type", "text/html"))
        .body(ctx.render_once().unwrap())
}

#[get("/novels/{novelId}/subtitles/{storyId}")]
pub async fn frontend_content(path: web::Path<(u64, u64)>, query: web::Query<crate::api_endpoint::ApiContentQueryParams>) -> impl Responder {
    let path = path.into_inner();
    let novel_id = path.0;

    let commit_id: Option<&str> = if query.commit_id.is_some() { Some(query.commit_id.as_ref().unwrap()) } else { None };

    let toc_info = load_toc_by_id(novel_id, None, None, commit_id).await.unwrap();

    let story_id = path.1;
    let content = load_content(novel_id, story_id, None, None, Some(toc_info.clone()), commit_id).await;
    if content.is_err() {
        eprintln!("Failed to parse content: {} in novel {}", story_id, novel_id);
        return HttpResponse::InternalServerError().body("Failed to parse content");
    }
    let content = content.unwrap();

    let mut introduction = content.element.introduction;
    let mut body = content.element.body;
    let mut postscript = content.element.postscript;

    if content.element.data_type == "text" {
        introduction = introduction.replace("\n", "<br />");
        body = body.replace("\n", "<br />");
        postscript = postscript.replace("\n", "<br />");
    }

    let reader_info = ApiReaderInfo {
        element: ApiElement {
            introduction: introduction.clone(),
            body: body.clone(),
            postscript: postscript.clone(),
        },
        novel_title: toc_info.title,
        novel_author: toc_info.author,
        novel_total_subtitles: toc_info.subtitles.len() as u64,
    };

    let ctx = StoryTemplate {
        html_title: format!("{} - {}", &content.subtitle, &reader_info.novel_title),
        novel_id,
        story_id,
        total_story_no: reader_info.novel_total_subtitles,
        chapter: content.chapter.clone(),
        subchapter: content.subchapter,
        subtitle: content.subtitle.clone(),
        subdate: content.subdate.clone(),
        subupdate: content.subupdate,
        reader_info: Some(reader_info),
    };

    HttpResponse::Ok()
        .insert_header(("Cache-Control", if commit_id.is_some() {"public, max-age=31536000"} else {"public, max-age=600"}))
        .insert_header(("Content-type", "text/html"))
        .body(ctx.render_once().unwrap())
}

#[get("/novels/{novelId}/revisions")]
pub async fn frontend_novel_revision(path: web::Path<(u64, )>) -> impl Responder {
    let path = path.into_inner();
    let novel_id = path.0;

    let novel_info = get_db_novel_info_by_id(novel_id, None, None).await.unwrap();
    let commits = load_toc_histories(novel_id, None, Some(novel_info.clone()), None).await.unwrap();

    let ctx = NovelRevisionTemplate {
        html_title: format!("過去ログ一覧 - {}", &novel_info.title),
        novel_id,
        title: novel_info.title,
        author: novel_info.author,
        revisions: commits.iter().map(|i| FrontendCommitInfo {
            commit_id: i.commit_id.clone(),
            commit_message: i.commit_message.clone(),
            commit_date: i.commit_date.timestamp() as u64,
        }).collect(),
    };

    HttpResponse::Ok()
        .insert_header(("Cache-Control", "public, max-age=600"))
        .insert_header(("Content-type", "text/html"))
        .body(ctx.render_once().unwrap())
}

#[get("/search/novel")]
pub async fn frontend_index_search_novel(query: web::Query<ApiFullTextSearchQueryParams>) -> impl Responder {
    #[cfg(feature = "full-text")]
    {
        let query = query.query.clone();
        let search_res = crate::full_text_novel::novel_index_search(&(query.unwrap_or("*".to_string()))).unwrap();

        let mut ctx = NovelSearchTemplate {
            html_title: "小説検索".to_string(),
            data: Vec::new(),
        };

        for res in search_res {
            let novel_id = res;

            let v = get_db_novel_info_by_id(novel_id, None, None).await.unwrap();
            let data = FrontendNovelInfo {
                id: v.id,
                title: v.title.clone(),
                author: v.author.clone(),
                general_lastup: v.general_lastup.timestamp() as u64,
                sitename: v.sitename.clone(),
                toc_url: v.toc_url.clone(),
            };

            ctx.data.push(data);
        }

        HttpResponse::Ok()
            .insert_header(("Cache-Control", "public, max-age=600"))
            .insert_header(("Content-type", "text/html"))
            .body(ctx.render_once().unwrap())
    }
    #[cfg(not(feature = "full-text"))]
    {
        HttpResponse::BadRequest()
            .body("This server isn't support full text search")
    }
}

#[get("/search/story")]
pub async fn frontend_index_search_story(query: web::Query<crate::api_endpoint::ApiFullTextSearchQueryParams>) -> impl Responder {
    #[cfg(feature = "full-text")]
    {
        let query = query.query.clone();
        let search_res = crate::full_text::index_search(&(query.unwrap_or("*".to_string()))).unwrap();

        let mut ctx = StorySearchTemplate {
            html_title: "本文検索".to_string(),
            data: Vec::new(),
        };

        for res in search_res {
            let novel_id = res.0;
            let story_id = res.1;

            let novel_info = get_db_novel_info_by_id(novel_id, None, None).await.unwrap();
            let v = novel_info.clone();
            let fe_novel_info = FrontendNovelInfo {
                id: v.id,
                title: v.title.clone(),
                author: v.author.clone(),
                general_lastup: v.general_lastup.timestamp() as u64,
                sitename: v.sitename.clone(),
                toc_url: v.toc_url.clone(),
            };

            let toc_info = load_toc_by_id(novel_id, None, Some(novel_info.clone()), None).await.unwrap();
            let v = &toc_info.subtitles[story_id as usize];

            let data = FrontendSubtitleSearchResult {
                index: v.index.clone().parse::<u64>().unwrap(),
                chapter: v.chapter.clone(),
                subchapter: v.subchapter.clone(),
                subtitle: v.subtitle.clone(),
                subdate: v.subdate.clone(),
                subupdate: v.subupdate.clone(),
                novel_info: fe_novel_info,
            };

            ctx.data.push(data);
        }

        HttpResponse::Ok()
            .insert_header(("Cache-Control", "public, max-age=600"))
            .insert_header(("Content-type", "text/html"))
            .body(ctx.render_once().unwrap())
    }
    #[cfg(not(feature = "full-text"))]
    {
        HttpResponse::BadRequest()
            .body("This server isn't support full text search")
    }
}
