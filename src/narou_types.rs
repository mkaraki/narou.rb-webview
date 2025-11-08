use chrono::{DateTime, Utc};
use serde::{Deserialize, Serialize};

#[derive(Debug, Default, Clone, Serialize, Deserialize)]
pub struct NovelInfo {
    pub id: u64,
    pub author: String,
    pub title: String,
    pub file_title: String,
    pub toc_url: String,
    pub sitename: String,
    pub novel_type: u64,
    pub end: bool,
    pub last_update: DateTime<Utc>,
    pub new_arrivals_date: DateTime<Utc>,
    pub use_subdirectory: bool,
    pub general_firstup: DateTime<Utc>,
    pub novelupdated_at: DateTime<Utc>,
    pub general_lastup: DateTime<Utc>,
    pub length: Option<u64>,
    pub suspend: bool,
    pub general_all_no: u64,
    pub tags: Option<Vec<String>>,
    pub last_check_date: Option<DateTime<Utc>>,
    pub last_mail_date: Option<DateTime<Utc>>,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct Toc {
    pub title: String,
    pub author: String,
    pub toc_url: String,
    pub story: String,
    pub subtitles: Vec<Subtitle>,
}

/// `subtitles`リストの各要素に対応する構造体
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct Subtitle {
    pub index: String, // YAMLでは '1' とクォートされているためStringにします
    pub href: String,
    pub chapter: Option<String>,
    pub subchapter: Option<String>,
    pub subtitle: String,
    pub file_subtitle: String,
    pub subdate: String, // HTMLタグが含まれるためStringにします
    pub subupdate: String,
    pub download_time: DateTime<Utc>,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct Story {
    /// The index, represented as a string.
    pub index: String,
    /// The corresponding HTML file.
    pub href: String,
    /// The main chapter title.
    pub chapter: Option<String>,
    /// The subchapter title (can be empty).
    pub subchapter: Option<String>,
    /// The main subtitle.
    pub subtitle: String,
    /// The subtitle used for the file.
    #[serde(rename = "file_subtitle")]
    pub file_subtitle: String,
    /// The publication date string (contains HTML).
    pub subdate: String,
    /// The update date string.
    pub subupdate: String,
    /// The nested element data.
    pub element: Element,
}

/// Represents the nested 'element' object.
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct Element {
    /// The type of data (e.g., "html").
    #[serde(rename = "data_type")]
    pub data_type: String,
    /// The introduction text (can be empty).
    pub introduction: String,
    /// The postscript text (can be empty).
    pub postscript: String,
    /// The main body content (contains HTML).
    pub body: String,
}
