use std::{env, io};
use crate::narou_parser::{load_content, load_index, load_toc_by_id};
use tantivy::{IndexReader, IndexWriter, ReloadPolicy};
use tantivy::index::{Index};
use tantivy::query::{QueryParser};
use tantivy::schema::{*, document::TantivyDocument};
use tantivy::collector::TopDocs;
use tantivy::directory::MmapDirectory;
use cached::proc_macro::once;

pub fn set_body_text_field_option() -> TextOptions {
    TextOptions::default()
            .set_indexing_options(
                TextFieldIndexing::default()
                    .set_tokenizer("lang_ja")
                    .set_index_option(IndexRecordOption::WithFreqsAndPositions),
            )
            // .set_stored() // Store real text is not required (Use yaml data source)
}

pub fn set_title_text_field_option() -> TextOptions {
    set_body_text_field_option()
}

fn get_schema() -> Schema {
    let mut schema_builder = Schema::builder();
    schema_builder.add_bytes_field("novel_story_id", STORED);
    schema_builder.add_u64_field("novel_id", INDEXED | STORED);
    schema_builder.add_u64_field("story_id", INDEXED | STORED);
    schema_builder.add_u64_field("download_time", STORED);

    schema_builder.add_text_field("chapter", set_title_text_field_option());
    schema_builder.add_text_field("subchapter", set_title_text_field_option());
    schema_builder.add_text_field("subtitle", set_title_text_field_option());

    schema_builder.add_text_field("introduction", set_body_text_field_option());
    schema_builder.add_text_field("body", set_body_text_field_option());
    schema_builder.add_text_field("postscript", set_body_text_field_option());
    let schema = schema_builder.build();

    schema
}

#[once()]
pub fn open_index() -> Result<Index, ()> {
    use lindera::dictionary::DictionaryKind;
    use lindera::{dictionary::load_embedded_dictionary, mode::Mode, segmenter::Segmenter};
    use lindera_tantivy::tokenizer::LinderaTokenizer;

    let schema = get_schema();

    let index_path = env::var("INDEX_PATH").unwrap_or("/index_data".to_string());
    println!("INDEX_PATH: {}", &index_path);
    let dir = MmapDirectory::open(index_path).unwrap();
    let index = Index::open_or_create(dir, schema.clone()).unwrap();

    let mode = Mode::Normal;
    let dictionary = load_embedded_dictionary(DictionaryKind::IPADICNEologd).unwrap();
    let user_dictionary = None;
    let segmenter = Segmenter::new(mode, dictionary, user_dictionary);
    let tokenizer = LinderaTokenizer::from_segmenter(segmenter);

    index.tokenizers().register("lang_ja", tokenizer);

    Ok(index)
}

fn get_novel_story_id(novel_id: u64, story_id: u64) -> Vec<u8> {
    let mut novel_story_id = vec![];
    novel_story_id.extend_from_slice(&novel_id.to_be_bytes());
    novel_story_id.extend_from_slice(&story_id.to_be_bytes());

    novel_story_id
}

pub async fn index_all() -> Result<(), ()> {
    println!("Preparing schema");
    let schema = get_schema();

    let field_novel_story_id = schema.get_field("novel_story_id").unwrap();
    let field_novel_id = schema.get_field("novel_id").unwrap();
    let field_story_id = schema.get_field("story_id").unwrap();
    let field_download_time = schema.get_field("download_time").unwrap();

    let field_chapter = schema.get_field("chapter").unwrap();
    let field_subchapter = schema.get_field("subchapter").unwrap();
    let field_subtitle = schema.get_field("subtitle").unwrap();

    let field_introduction = schema.get_field("introduction").unwrap();
    let field_body = schema.get_field("body").unwrap();
    let field_postscript = schema.get_field("postscript").unwrap();

    println!("Preparing index");
    let index = open_index().unwrap();

    let mut writer: IndexWriter = index.writer(50_000_000 /* 50MB budget */).unwrap();
    let reader: IndexReader = index
        .reader_builder()
        .try_into().unwrap();
    let searcher = reader.searcher();
    let query_parser = QueryParser::for_index(&index, vec![]);

    println!("Reading novel list");
    let novels = load_index(None).await.unwrap();

    let novel_len = novels.len();
    for novel_idx in 0..novel_len {
        let novel_id = novels[novel_idx].id;
        let toc = load_toc_by_id(novel_id, None, Some(novels[novel_idx].clone()), None).await;
        if toc.is_err() {
            eprintln!("ERR: Novel: {} ({}, idx: {}), unable to read toc", novel_id, novels[novel_idx].title, novel_idx);
            continue;
        }
        let toc = toc.unwrap();

        println!("Novel: {} ({}, idx: {}/{})", novel_id, toc.title, novel_idx, novel_len);

        let subtitle_len = toc.subtitles.len();
        for story_id in 0..subtitle_len {
            let content = load_content(novel_id as u64, story_id as u64, None, Some(novels[novel_idx].clone()), Some(toc.clone()), None).await;
            if content.is_err() {
                eprintln!("  ERR Story: {} ({}), unable to read content", story_id, toc.subtitles[story_id].subtitle);
                continue;
            }
            let content = content.unwrap();

            let novel_story_id = get_novel_story_id(novel_id as u64, story_id as u64);

            let download_time: u64 = toc.subtitles[story_id].download_time.timestamp() as u64;
            let mut is_update = false;
            let mut is_updated = false;

            let query = query_parser.parse_query(&format!("novel_id:{} AND story_id:{}", novel_id, story_id)).unwrap();
            let top_docs = searcher.search(&query, &TopDocs::with_limit(10)).unwrap();
            for (_score, doc_address) in top_docs {
                let retrieved_doc: TantivyDocument = searcher.doc(doc_address).unwrap();
                let db_novel_id = retrieved_doc.get_first(field_novel_id).unwrap().as_u64().unwrap();
                let db_story_id = retrieved_doc.get_first(field_story_id).unwrap().as_u64().unwrap();

                if (novel_id as u64) != db_novel_id && (story_id as u64) != db_story_id {
                    // Not same novel, story. This must not occurs.
                    continue;
                }

                let db_download_time = retrieved_doc.get_first(field_download_time).unwrap().as_u64().unwrap();

                if download_time != db_download_time {
                    // DB's data is old or invalid.
                    is_update = true;
                    let delete_term = Term::from_field_bytes(field_novel_story_id, &novel_story_id);
                    writer.delete_term(delete_term);
                    let commit_res = writer.commit();
                    if commit_res.is_err() {
                        eprintln!("  ERR Story: {} ({}), unable to delete previous index (commit error)", story_id, toc.subtitles[story_id].subtitle);
                    }

                } else if download_time == db_download_time {
                    is_updated = true;
                    continue;
                }
            }

            let subtitle = &content.subtitle;

            if is_update {
                println!("  Update: {}/{} ({})", story_id, subtitle_len, subtitle);
            } else if is_updated {
                println!("  Keep  : {}/{} ({})", story_id, subtitle_len, subtitle);
            } else {
                println!("  New   : {}/{} ({})", story_id, subtitle_len, subtitle);
            }

            let mut introduction_reader = io::Cursor::new(content.element.introduction.into_bytes());
            let introduction_text = html2text::from_read(&mut introduction_reader, 65535).unwrap();
            let mut body_reader = io::Cursor::new(content.element.body.into_bytes());
            let body_text = html2text::from_read(&mut body_reader, 65535).unwrap();
            let mut postscript_reader = io::Cursor::new(content.element.postscript.into_bytes());
            let postscript_text = html2text::from_read(&mut postscript_reader, 65535).unwrap();

            let mut document = TantivyDocument::default();

            document.add_bytes(field_novel_story_id, &novel_story_id);
            document.add_u64(field_novel_id, novel_id as u64);
            document.add_u64(field_story_id, story_id as u64);
            document.add_u64(field_download_time, download_time);

            if content.chapter.is_some() {
                document.add_text(field_chapter, &content.chapter.unwrap());
            }
            if content.subchapter.is_some() {
                document.add_text(field_subchapter, &content.subchapter.unwrap());
            }

            document.add_text(field_subtitle, subtitle);
            document.add_text(field_introduction, introduction_text);
            document.add_text(field_body, body_text);
            document.add_text(field_postscript, postscript_text);

            writer.add_document(document).unwrap();

            let commit_res = writer.commit();
            if commit_res.is_err() {
                eprintln!("  ERR Story: {} ({}), unable to commit", story_id, toc.subtitles[story_id].subtitle);
            }
        }
    }


    Ok(())
}


pub fn index_search(query: &str) -> Result<Vec<(u64, u64)>, ()> {
    let schema = get_schema();

    //let field_novel_story_id = schema.get_field("novel_story_id").unwrap();
    let field_novel_id = schema.get_field("novel_id").unwrap();
    let field_story_id = schema.get_field("story_id").unwrap();
    //let field_download_time = schema.get_field("download_time").unwrap();

    let field_chapter = schema.get_field("chapter").unwrap();
    let field_subchapter = schema.get_field("subchapter").unwrap();
    let field_subtitle = schema.get_field("subtitle").unwrap();

    let field_introduction = schema.get_field("introduction").unwrap();
    let field_body = schema.get_field("body").unwrap();
    let field_postscript = schema.get_field("postscript").unwrap();

    let index = open_index().unwrap();

    let reader: IndexReader = index
        .reader_builder()
        .reload_policy(ReloadPolicy::OnCommitWithDelay)
        .try_into().unwrap();
    let searcher = reader.searcher();
    let query_parser = QueryParser::for_index(&index, vec![field_chapter, field_subchapter, field_subtitle, field_introduction, field_body, field_postscript]);

    let mut result: Vec<(u64, u64)> = Vec::new();

    let query = query_parser.parse_query(query).unwrap();
    let top_docs = searcher.search(&query, &TopDocs::with_limit(100)).unwrap();
    for (_score, doc_address) in top_docs {
        let retrieved_doc: TantivyDocument = searcher.doc(doc_address).unwrap();
        let db_novel_id = retrieved_doc.get_first(field_novel_id).unwrap().as_u64().unwrap();
        let db_story_id = retrieved_doc.get_first(field_story_id).unwrap().as_u64().unwrap();

        result.push((db_novel_id, db_story_id));
    }

    Ok(result)
}
