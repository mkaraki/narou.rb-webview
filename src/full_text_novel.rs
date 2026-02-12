use std::{env};
use crate::narou_parser::{load_index, load_toc_by_id};
use tantivy::{IndexReader, IndexWriter, ReloadPolicy};
use tantivy::index::{Index};
use tantivy::query::{QueryParser};
use tantivy::schema::{*, document::TantivyDocument};
use tantivy::collector::TopDocs;
use tantivy::directory::MmapDirectory;
use cached::proc_macro::once;

use crate::full_text::{set_body_text_field_option, set_title_text_field_option};

fn get_schema() -> Schema {
    let mut schema_builder = Schema::builder();
    schema_builder.add_u64_field("novel_id", INDEXED | STORED);
    schema_builder.add_u64_field("general_lastup", STORED);

    schema_builder.add_text_field("title", set_title_text_field_option());
    schema_builder.add_text_field("story", set_body_text_field_option());

    let schema = schema_builder.build();

    schema
}

#[once()]
pub fn open_novel_index() -> Result<Index, ()> {
    use lindera::dictionary::DictionaryKind;
    use lindera::{dictionary::load_embedded_dictionary, mode::Mode, segmenter::Segmenter};
    use lindera_tantivy::tokenizer::LinderaTokenizer;

    let schema = get_schema();

    let index_path = env::var("NOVEL_INDEX_PATH").unwrap_or("/novel_index_data".to_string());
    println!("NOVEL_INDEX_PATH: {}", &index_path);
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

pub async fn novel_index_all() -> Result<(), ()> {
    println!("Preparing schema");
    let schema = get_schema();

    let field_novel_id = schema.get_field("novel_id").unwrap();
    let field_general_lastup = schema.get_field("general_lastup").unwrap();

    let field_title = schema.get_field("title").unwrap();
    let field_story = schema.get_field("story").unwrap();

    println!("Preparing index");
    let index = open_novel_index().unwrap();

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

        let general_lastup: u64 = novels[novel_idx].general_lastup.timestamp() as u64;
        let mut is_update = false;
        let mut is_updated = false;

        let query = query_parser.parse_query(&format!("novel_id:{}", novel_id)).unwrap();
        let top_docs = searcher.search(&query, &TopDocs::with_limit(10)).unwrap();
        for (_score, doc_address) in top_docs {
            let retrieved_doc: TantivyDocument = searcher.doc(doc_address).unwrap();
            let db_novel_id = retrieved_doc.get_first(field_novel_id).unwrap().as_u64().unwrap();

            if (novel_id as u64) != db_novel_id {
                // Not same novel, story. This must not occurs.
                continue;
            }

            let db_general_lastup = retrieved_doc.get_first(field_general_lastup).unwrap().as_u64().unwrap();

            if general_lastup != db_general_lastup {
                // DB's data is old or invalid.
                is_update = true;
                let delete_term = Term::from_field_u64(field_novel_id, novel_id);
                writer.delete_term(delete_term);
                let commit_res = writer.commit();
                if commit_res.is_err() {
                    eprintln!("ERR Novel: {} ({}), unable to delete previous index (commit error)", novel_id, toc.title);
                }
            } else if general_lastup == db_general_lastup {
                is_updated = true;
                continue;
            }
        }

        if is_update {
            println!("Novel Upd: {} ({}, idx: {}/{})", novel_id, toc.title, novel_idx, novel_len);
        } else if is_updated {
            println!("Novel Nop: {} ({}, idx: {}/{})", novel_id, toc.title, novel_idx, novel_len);
        } else {
            println!("Novel New: {} ({}, idx: {}/{})", novel_id, toc.title, novel_idx, novel_len);
        }

        let mut document = TantivyDocument::default();

        document.add_u64(field_novel_id, novel_id as u64);
        document.add_u64(field_general_lastup, general_lastup);

        document.add_text(field_title, toc.title.clone());
        document.add_text(field_story, toc.story);

        writer.add_document(document).unwrap();

        let commit_res = writer.commit();
        if commit_res.is_err() {
            eprintln!("ERR Novel: {} ({}), unable to commit", novel_id, toc.title);
        }
    }


    Ok(())
}


pub fn novel_index_search(query: &str) -> Result<Vec<u64>, ()> {
    let schema = get_schema();

    let field_novel_id = schema.get_field("novel_id").unwrap();
    //let field_general_lastup = schema.get_field("general_lastup").unwrap();

    let field_title = schema.get_field("title").unwrap();
    let field_story = schema.get_field("story").unwrap();

    let index = open_novel_index().unwrap();

    let reader: IndexReader = index
        .reader_builder()
        .reload_policy(ReloadPolicy::OnCommitWithDelay)
        .try_into().unwrap();
    let searcher = reader.searcher();
    let query_parser = QueryParser::for_index(&index, vec![field_title, field_story]);

    let mut result: Vec<u64> = Vec::new();

    let query = query_parser.parse_query(query).unwrap();
    let top_docs = searcher.search(&query, &TopDocs::with_limit(100)).unwrap();
    for (_score, doc_address) in top_docs {
        let retrieved_doc: TantivyDocument = searcher.doc(doc_address).unwrap();
        let db_novel_id = retrieved_doc.get_first(field_novel_id).unwrap().as_u64().unwrap();

        result.push(db_novel_id);
    }

    Ok(result)
}
