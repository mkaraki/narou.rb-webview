use sailfish::{TemplateOnce};
use serde::Serialize;
use crate::api_types::{ApiReaderInfo, ApiSubtitle};

#[derive(Debug, Clone)]
pub struct FrontendNovelInfo {
    pub id: u64,
    pub title: String,
    pub general_lastup: u64,
    pub author: String,
    pub sitename: String,
    pub toc_url: String,
}

#[derive(TemplateOnce)]
#[template(path = "novels.html.stpl")]
pub struct NovelsTemplate {
    pub(crate) html_title: String,
    pub(crate) novels: Vec<FrontendNovelInfo>,
    pub(crate) total: u64,
    pub(crate) page: u64,
}

#[derive(TemplateOnce)]
#[template(path = "stories.html.stpl")]
pub struct StoriesTemplate {
    pub(crate) html_title: String,
    pub(crate) novel_id: u64,
    pub(crate) title: String,
    pub(crate) author: String,
    pub(crate) story: String,
    pub(crate) subtitles: Vec<ApiSubtitle>,
    pub(crate) total: u64,
    pub(crate) page: u64,
}

#[derive(TemplateOnce)]
#[template(path = "story.html.stpl")]
pub struct StoryTemplate {
    pub(crate) html_title: String,
    pub(crate) novel_id: u64,
    pub(crate) story_id: u64,
    pub(crate) total_story_no: u64,
    pub(crate) chapter: Option<String>,
    pub(crate) subchapter: Option<String>,
    pub(crate) subtitle: String,
    pub(crate) reader_info: Option<ApiReaderInfo>,
}

#[derive(Debug, Clone, Serialize)]
pub struct FrontendCommitInfo {
    pub commit_id: String,
    pub commit_message: String,
    pub commit_date: u64,
}

#[derive(TemplateOnce)]
#[template(path = "revisions.html.stpl")]
pub struct NovelRevisionTemplate {
    pub html_title: String,
    pub novel_id: u64,
    pub title: String,
    pub author: String,
    pub revisions: Vec<FrontendCommitInfo>,
}

#[derive(TemplateOnce)]
#[template(path = "novel_search.html.stpl")]
pub struct NovelSearchTemplate {
    pub html_title: String,
    pub data: Vec<FrontendNovelInfo>,
}

#[derive(Debug, Clone)]
pub struct FrontendSubtitleSearchResult {
    pub index: u64,
    pub subtitle: String,
    pub novel_info: FrontendNovelInfo,
}

#[derive(TemplateOnce)]
#[template(path = "story_search.html.stpl")]
pub struct StorySearchTemplate {
    pub html_title: String,
    pub data: Vec<FrontendSubtitleSearchResult>,
}

