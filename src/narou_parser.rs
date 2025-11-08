use crate::narou_types::*;

use std::collections::HashMap;
use std::path::{Path, PathBuf};
use git2::{DiffOptions, Oid, Repository, RepositoryOpenFlags};
use tokio::fs::File;
use tokio::io::AsyncReadExt;
use crate::narou_types::Story;
use std::time::Duration;
use cached::proc_macro::{cached, once};
use chrono::{DateTime, Utc};
use serde::Serialize;

#[once()]
fn get_narou_root() -> String {
    let narou_root: &'static str = option_env!("NAROU_ROOT").unwrap_or("/data");
    return narou_root.to_string();
}

#[tracing::instrument]
fn load_yaml_bin_from_commit(path: &str, commit_id: &str) -> Result<Vec<u8>, ()> {
    let narou_root = get_narou_root();

    let mut relative_path = &path[narou_root.len()..];
    if relative_path.starts_with("/") || relative_path.starts_with("\\") {
        relative_path = &relative_path[1..];
    }

    let repo = Repository::open(narou_root).unwrap();
    //let oid = Oid::from_str(&*commit_id).unwrap();
    //let commit = repo.find_commit(oid).unwrap();
    let obj = repo.revparse_single(&*commit_id).unwrap();
    let commit = obj.peel_to_commit().unwrap();
    let tree = commit.tree().unwrap();
    let entry = tree.get_path(Path::new(relative_path)).unwrap();
    let object = entry.to_object(&repo).unwrap();

    if let Some(blob) = object.as_blob() {
        let buffer = blob.content();
        return Ok(buffer.to_vec());
    } else {
        return Err(());
    }
}

#[tracing::instrument]
async fn load_yaml_bin(path: &str, commit_id: Option<&str>) -> Result<Vec<u8>, ()> {
    match commit_id {
        Some(commit_id) => {
            load_yaml_bin_from_commit(path, commit_id)
        },
        None => {
            let mut file = File::open(path).await.unwrap();
            let mut buffer = Vec::new();
            file.read_to_end(&mut buffer).await.unwrap();
            Ok(buffer)
        }
    }
}

#[tracing::instrument]
#[cached(time = 600, result = true, key = "String", convert = r#"{ commit_id.unwrap_or("").parse().unwrap() }"#)]
pub async fn load_index(commit_id: Option<&str>) -> Result<Vec<NovelInfo>, ()> {
    let narou_root = get_narou_root();
    let narou_path = Path::new(&narou_root);


    let narou_db = narou_path.join(".narou/database.yaml");
    let narou_db = narou_db.to_str().unwrap();
    let yaml_bin = load_yaml_bin(narou_db, commit_id).await.unwrap();
    let novel_info: HashMap<u64, NovelInfo> = serde_saphyr::from_slice(yaml_bin.as_slice()).unwrap();

    Ok(novel_info.values().cloned().collect())
}

#[tracing::instrument]
pub async fn get_db_novel_info_by_id(id: u64, loaded_index: Option<Vec<NovelInfo>>, commit_id: Option<&str>) -> Option<NovelInfo> {
    let grand_index;
    if loaded_index.is_none() {
        grand_index = load_index(commit_id).await.unwrap();
    } else {
        grand_index = loaded_index.unwrap();
    }

    let d = grand_index.iter().find(|v| v.id == id);
    if d.is_some() {
        Some(d.unwrap().clone())
    } else {
        None
    }
}

async fn get_toc_path(id: u64, loaded_index: Option<Vec<NovelInfo>>, novel_info: Option<NovelInfo>, commit_id: Option<&str>) -> Result<PathBuf, ()> {
    let narou_root = get_narou_root();
    let narou_path = Path::new(&narou_root);

    let n_info;
    if novel_info.is_none() {
        n_info = get_db_novel_info_by_id(id, loaded_index, commit_id.clone()).await.unwrap();
    } else {
        n_info = novel_info.unwrap();
    }
    let toc_path = narou_path.join("小説データ").join(&*n_info.sitename).join(&*n_info.file_title).join("toc.yaml");
    Ok(toc_path)
}

#[tracing::instrument]
pub async fn load_toc_by_id(id: u64, loaded_index: Option<Vec<NovelInfo>>, novel_info: Option<NovelInfo>, commit_id: Option<&str>) -> Result<Toc, ()> {
    let toc_path = get_toc_path(id, loaded_index, novel_info, commit_id).await.unwrap();
    let toc_path = toc_path.to_str().unwrap();

    let yaml_bin = load_yaml_bin(&*toc_path, commit_id).await.unwrap();
    let toc: Toc = serde_saphyr::from_slice(yaml_bin.as_slice()).unwrap();

    Ok(toc)
}

#[derive(Debug, Clone, Serialize)]
pub struct CommitInfo {
    pub commit_id: String,
    pub commit_message: String,
    pub commit_date: DateTime<Utc>,
}

#[tracing::instrument]
pub async fn load_toc_histories(id: u64, loaded_index: Option<Vec<NovelInfo>>, novel_info: Option<NovelInfo>, commit_id: Option<&str>) -> Result<Vec<CommitInfo>, ()> {
    let narou_root: &str = &*get_narou_root();

    let toc_path = get_toc_path(id, loaded_index, novel_info, commit_id).await.unwrap();
    let toc_path = toc_path.to_str().unwrap();
    let repo = Repository::open(narou_root).unwrap();

    let mut relative_path = &toc_path[narou_root.len()..];
    if relative_path.starts_with("/") || relative_path.starts_with("\\") {
        relative_path = &relative_path[1..];
    }

    let mut revwalk = repo.revwalk().unwrap();
    revwalk.set_sorting(git2::Sort::TIME).unwrap();
    revwalk.push_head().unwrap();

    let mut commits: Vec<CommitInfo> = Vec::new();

    for oid in revwalk {
        let oid = oid.unwrap();
        let commit = repo.find_commit(oid).unwrap();
        let tree = commit.tree().unwrap();

        let commit_date = DateTime::from_timestamp_secs(commit.time().seconds()).unwrap();

        if commit.parent_count() == 0 {
            commits.push(CommitInfo {
                commit_id: oid.to_string(),
                commit_message: commit.message().unwrap().to_string(),
                commit_date,
            });
            continue;
        }

        let parent_tree = Some(&commit.parent(0).unwrap().tree().unwrap());

        let mut diff_opt = DiffOptions::new();
        diff_opt.pathspec(relative_path);
        diff_opt.show_binary(true);

        let diff = repo.diff_tree_to_tree(
            parent_tree,
            Some(&tree),
            Some(&mut diff_opt)
        ).unwrap();

        if diff.deltas().len() > 0 {
            commits.push(CommitInfo {
                commit_id: oid.to_string(),
                commit_message: commit.message().unwrap().to_string(),
                commit_date,
            });
        }
    }

    Ok(commits)
}

#[tracing::instrument]
pub async fn load_content(novel_id: u64, story_id: u64, loaded_index: Option<Vec<NovelInfo>>, novel_info: Option<NovelInfo>, loaded_toc: Option<Toc>, commit_id: Option<&str>) -> Result<Story, ()> {
    let narou_root = get_narou_root();
    let narou_path = Path::new(&narou_root);

    let n_info;
    if novel_info.is_none() {
        n_info = get_db_novel_info_by_id(novel_id, loaded_index.clone(), commit_id.clone()).await.unwrap();
    } else {
        n_info = novel_info.unwrap();
    }

    let toc;
    if loaded_toc.is_none() {
        toc = load_toc_by_id(novel_id, loaded_index, Some(n_info.clone()), commit_id.clone()).await.unwrap();
    } else {
        toc = loaded_toc.unwrap();
    }

    let subtitle = &toc.subtitles[story_id as usize];

    let novel_content_dir = narou_path.join("小説データ").join(&*n_info.sitename).join(&*n_info.file_title).join("本文");
    let novel_content_path = novel_content_dir.join(format!("{} {}.yaml", subtitle.index, subtitle.file_subtitle));
    let novel_content_path = novel_content_path.to_str().unwrap();

    let yaml_bin = load_yaml_bin(&*novel_content_path, commit_id).await.unwrap();
    let story: Story = serde_saphyr::from_slice(yaml_bin.as_slice()).unwrap();

    Ok(story)
}
