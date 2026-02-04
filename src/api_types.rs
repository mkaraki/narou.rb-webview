use serde::{Serialize};
use chrono::{DateTime, Utc};
use crate::narou_parser::CommitInfo;
use crate::narou_types::{NovelInfo, Story, Toc};

#[derive(Debug, Clone, Serialize)]
pub struct ApiNovelList {
    pub novels: Vec<ApiNovelInfo>,
    pub total: u64,
    pub page: u64,
}

#[derive(Debug, Clone, Serialize)]
pub struct ApiNovelInfo {
    pub id: u64,
    pub title: String,
    pub general_lastup: DateTime<Utc>,
    pub author: String,
    pub sitename: String,
    pub toc_url: String,
}

#[derive(Debug, Clone, Serialize)]
pub struct ApiStories {
    pub title: String,
    pub author: String,
    pub story: String,
    pub subtitles: Vec<ApiSubtitle>,
}

#[derive(Debug, Clone, Serialize)]
pub struct ApiNovelRevision {
    pub title: String,
    pub author: String,
    pub revisions: Vec<CommitInfo>,
}

#[derive(Debug, Clone, Serialize)]
pub struct ApiSubtitle {
    pub index: u64,
    pub chapter: Option<String>,
    pub subchapter: Option<String>,
    pub subtitle: String,
    pub subdate: String,
    pub subupdate: String,
    pub reader_info: Option<ApiReaderInfo>,
}

#[derive(Debug, Clone, Serialize)]
pub struct ApiReaderInfo {
    pub element: ApiElement,
    pub novel_title: String,
    pub novel_author: String,
    pub novel_total_subtitles: u64,
}

#[derive(Debug, Clone, Serialize)]
pub struct ApiElement {
    pub introduction: String,
    pub body: String,
    pub postscript: String,
}

#[derive(Debug, Clone, Serialize)]
pub struct InspectNovel {
    pub db_item: Option<NovelInfo>,
    pub toc: Option<Toc>,
    pub story: Option<Story>,
}