use actix_web::{get, web, HttpResponse, Responder};
use rayon::prelude::ParallelSliceMut;
use serde::Deserialize;
use crate::api_types::{ApiElement, ApiNovelInfo, ApiNovelList, ApiNovelRevision, ApiReaderInfo, ApiStories, ApiSubtitle, InspectNovel};
use crate::narou_parser::{get_db_novel_info_by_id, load_content, load_index, load_toc_by_id, load_toc_histories};
use crate::narou_types::{NovelInfo, Toc};

#[derive(Deserialize)]
struct ApiListQueryParams {
    p: Option<u64>,
    commit_id: Option<String>,
    sort: Option<String>,
    order: Option<String>,
    title_like: Option<String>,
    author_like: Option<String>,
    author_exact: Option<String>,
    tag: Option<String>,
}

async fn extract_api_list(query: web::Query<ApiListQueryParams>) -> Vec<NovelInfo> {
    let commit_id: Option<&str> = if query.commit_id.is_some() { Some(query.commit_id.as_ref().unwrap()) } else { None };

    let mut data: Vec<NovelInfo> = load_index(commit_id).await.unwrap();

    if let Some(sort) = query.sort.clone() {
        match sort.as_str() {
            "id" => data.par_sort_by_key(|v| v.id),
            "author" => data.par_sort_by_key(|v| v.author.clone()),
            "file_title" => data.par_sort_by_key(|v| v.file_title.clone()),
            "toc_url" => data.par_sort_by_key(|v| v.toc_url.clone()),
            "sitename" => data.par_sort_by_key(|v| v.sitename.clone()),
            "novel_type" => data.par_sort_by_key(|v| v.novel_type),
            "end" => data.par_sort_by_key(|v| v.end),
            "last_update" => data.par_sort_by_key(|v| v.last_update),
            "new_arrivals_date" => data.par_sort_by_key(|v| v.new_arrivals_date),
            "use_subdirectory" => data.par_sort_by_key(|v| v.use_subdirectory),
            "general_firstup" => data.par_sort_by_key(|v| v.general_firstup),
            "novelupdated_at" => data.par_sort_by_key(|v| v.novelupdated_at),
            "general_lastup" => data.par_sort_by_key(|v| v.general_lastup),
            "length" => data.par_sort_by_key(|v| v.length.unwrap_or(0)),
            "suspend" => data.par_sort_by_key(|v| v.suspend),
            "general_all_no" => data.par_sort_by_key(|v| v.general_all_no),
            "last_check_date" => data.par_sort_by_key(|v| v.last_check_date.unwrap_or_default()),
            "last_mail_date" => data.par_sort_by_key(|v| v.last_mail_date.unwrap_or_default()),
            _ => data.par_sort_by_key(|v| v.title.clone()), /* title */
        }
    } else {
        data.par_sort_by_key(|v| v.title.clone());
    }

    if let Some(order) = query.order.clone() {
        match order.to_lowercase().as_str() {
            "desc" => data.reverse(),
            _ => (),
        }
    }

    if let Some(title_like) = query.title_like.clone() {
        data.retain(|v| v.title.contains(&title_like));
    }

    if let Some(author_exact) = query.author_exact.clone() {
        data.retain(|v| v.author == author_exact);
    } else if let Some(author_like) = query.author_like.clone() {
        data.retain(|v| v.author.contains(&author_like));
    }
 
    data
}

#[get("/novels")]
pub async fn api_list(query: web::Query<ApiListQueryParams>) -> impl Responder {
    let has_commit_id = query.commit_id.is_some();
    
    let mut page = query.p.unwrap_or(1);
    if page == 0 { page = 1; }
    let skip = 100 * (page - 1u64);
    
    let data = extract_api_list(query).await;

    let res = ApiNovelList {
        total: data.len() as u64,
        page,
        novels: data.iter().skip(skip as usize).take(100).map(|v| ApiNovelInfo {
            id: v.id,
            title: v.title.clone(),
            author: v.author.clone(),
            general_lastup: v.general_lastup,
            sitename: v.sitename.clone(),
            toc_url: v.toc_url.clone(),
        }).collect(),
    };

    HttpResponse::Ok()
        .insert_header(("Cache-Control", if has_commit_id {"public, max-age=31536000"} else {"public, max-age=600"}))
        .json(res)
}

#[get("/inspect/novels")]
pub async fn api_list_inspect(query: web::Query<ApiListQueryParams>) -> impl Responder {
    let data = extract_api_list(query).await;

    HttpResponse::Ok()
        .json(data)
}

#[derive(Deserialize)]
struct ApiStoryQueryParams {
    commit_id: Option<String>,
    // Note: frontend not supported.
    order: Option<String>,
}

#[get("/novels/{novelId}/subtitles")]
pub async fn api_story(path: web::Path<(u64,)>, query: web::Query<ApiStoryQueryParams>) -> impl Responder {
    let path = path.into_inner();
    let novel_id = path.0;
    let commit_id: Option<&str> = if query.commit_id.is_some() { Some(query.commit_id.as_ref().unwrap()) } else { None };
    let toc = load_toc_by_id(novel_id, None, None, commit_id).await.unwrap();

    let data = ApiStories {
        title: toc.title.clone(),
        author: toc.author.clone(),
        story: toc.story.clone(),
        subtitles: toc.subtitles.iter().map(|v| ApiSubtitle {
            index: v.index.clone().parse::<u64>().unwrap(),
            chapter: v.chapter.clone(),
            subchapter: v.subchapter.clone(),
            subtitle: v.subtitle.clone(),
            subdate: v.subdate.clone(),
            subupdate: v.subupdate.clone(),
            reader_info: None,
        }).collect(),
    };

    // data.subtitles.par_sort_by_key(|v| v.index);
    //
    // if let Some(order) = query.order.clone() {
    //     match order.to_lowercase().as_str() {
    //         "desc" => data.subtitles.reverse(),
    //         _ => (),
    //     }
    // }

    HttpResponse::Ok()
        .insert_header(("Cache-Control", if commit_id.is_some() {"public, max-age=31536000"} else {"public, max-age=600"}))
        .json(data)
}

#[get("/inspect/novels/{novelId}/subtitles")]
pub async fn api_story_inspect(path: web::Path<(u64,)>, query: web::Query<ApiStoryQueryParams>) -> impl Responder {
    let path = path.into_inner();
    let novel_id = path.0;
    let commit_id: Option<&str> = if query.commit_id.is_some() { Some(query.commit_id.as_ref().unwrap()) } else { None };

    let novel_info = get_db_novel_info_by_id(novel_id, None, commit_id).await.unwrap();
    let toc = load_toc_by_id(novel_id, None, Some(novel_info.clone()), commit_id).await.unwrap();
    
    let data = InspectNovel {
        db_item: Some(novel_info),
        toc: Some(toc),
        story: None,
    };
    
    HttpResponse::Ok()
        .json(data)
}

#[get("/novels/{novelId}/revisions")]
pub async fn api_novel_revision(path: web::Path<(u64, )>) -> impl Responder {
    let path = path.into_inner();
    let novel_id = path.0;

    let novel_info = get_db_novel_info_by_id(novel_id, None, None).await.unwrap();
    let commits = load_toc_histories(novel_id, None, Some(novel_info.clone()), None).await.unwrap();

    let ret = ApiNovelRevision {
        title: novel_info.title,
        author: novel_info.author,
        revisions: commits,
    };

    HttpResponse::Ok()
        .insert_header(("Cache-Control", "public, max-age=600"))
        .json(ret)
}

#[derive(Deserialize)]
struct ApiContentQueryParams {
    commit_id: Option<String>,
}

#[get("/novels/{novelId}/subtitles/{storyId}")]
pub async fn api_content(path: web::Path<(u64, u64)>, query: web::Query<ApiContentQueryParams>) -> impl Responder {
    let path = path.into_inner();
    let novel_id = path.0;

    let commit_id: Option<&str> = if query.commit_id.is_some() { Some(query.commit_id.as_ref().unwrap()) } else { None };

    let toc_info = load_toc_by_id(novel_id, None, None, commit_id).await.unwrap();

    let story_id = path.1;
    let content = load_content(novel_id, story_id, None, None, Some(toc_info.clone()), commit_id).await.unwrap();

    let subtitle_info = ApiSubtitle {
        index: content.index.clone().parse::<u64>().unwrap(),
        chapter: content.chapter.clone(),
        subchapter: content.subchapter,
        subtitle: content.subtitle.clone(),
        subdate: content.subdate.clone(),
        subupdate: content.subupdate,
        reader_info: Some(ApiReaderInfo {
            element: ApiElement {
                introduction: content.element.introduction.clone(),
                body: content.element.body.clone(),
                postscript: content.element.postscript.clone()
            },
            novel_title: toc_info.title,
            novel_author: toc_info.author,
            novel_total_subtitles: toc_info.subtitles.len() as u64,
        })
    };

    HttpResponse::Ok()
        .json(subtitle_info)
}

#[get("/inspect/novels/{novelId}/subtitles/{storyId}")]
pub async fn api_content_inspect(path: web::Path<(u64, u64)>, query: web::Query<ApiContentQueryParams>) -> impl Responder {
    let path = path.into_inner();
    let novel_id = path.0;

    let commit_id: Option<&str> = if query.commit_id.is_some() { Some(query.commit_id.as_ref().unwrap()) } else { None };

    let novel_info = get_db_novel_info_by_id(novel_id, None, None).await.unwrap();
    let toc_info = load_toc_by_id(novel_id, None, Some(novel_info.clone()), commit_id).await.unwrap();

    let story_id = path.1;
    let content = load_content(novel_id, story_id, None, Some(novel_info.clone()), Some(toc_info.clone()), commit_id).await.unwrap();

    let data = InspectNovel {
        db_item: Some(novel_info),
        toc: Some(toc_info),
        story: Some(content),
    };

    HttpResponse::Ok()
        .json(data)
}
