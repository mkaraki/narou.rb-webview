<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . '/postconfig.php';

define('APCU_AVAILABLE', function_exists('apcu_enabled') && apcu_enabled());

function loadYaml(string $path): array
{
    if (APCU_AVAILABLE && !isset($_GET['no_cache']) && apcu_exists($path)) {
        return apcu_fetch($path);
    }

    $data = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($path));

    if (APCU_AVAILABLE) {
        apcu_store($path, $data, 1800);
    }

    return $data;
}

function loadIndex(bool $useapi = false): array
{
    return loadYaml(NAROU_DB_YAML);
}

function getIndexFileUpdateEpoch(): int
{
    return filemtime(NAROU_DB_YAML);
}

function loadIndexNovel(int $id, array $loadedindex = null): array
{
    if ($loadedindex === null) $loadedindex = loadIndex();

    if ($id >= count($loadedindex))
        die('該当の小説が見つかりませんでした。');

    return $loadedindex[$id];
}

function loadToc(int $id, array $loadedindex = null, array $indexdata = null): array
{
    if ($indexdata === null) $indexdata = loadIndexNovel($id, $loadedindex);

    $tocpath = NAROU_NOVEL_DIR . DIRECTORY_SEPARATOR . $indexdata['sitename'] . DIRECTORY_SEPARATOR . $indexdata['file_title'] . DIRECTORY_SEPARATOR . 'toc.yaml';
    return loadYaml($tocpath);
}

function loadContent(int $storyid, int $novelid, array $loadedtoc = null, array $loadedindex = null): array
{
    if ($loadedindex === null) $loadedindex = loadIndex();
    if ($loadedtoc === null) $loadedtoc = loadToc($novelid, $loadedindex);

    if ($storyid >= count($loadedtoc['subtitles']))
        die('該当の話が見つかりませんでした。');

    $novelcontentdir  = NAROU_NOVEL_DIR . DIRECTORY_SEPARATOR . $loadedindex[$novelid]['sitename'] . DIRECTORY_SEPARATOR . $loadedindex[$novelid]['file_title'] . DIRECTORY_SEPARATOR . '本文';
    $novelcontentpath = $novelcontentdir . DIRECTORY_SEPARATOR . $loadedtoc['subtitles'][$storyid]['index'] . ' ' . $loadedtoc['subtitles'][$storyid]['file_subtitle'] . '.yaml';
    return loadYaml($novelcontentpath);
}

function getLastModifiedNovelEpoch(array $indexNovel): int
{
    $lastupdt = $indexNovel['last_update'];
    return $lastupdt;
}

function getLastModifiedStoryEpoch(int $id, array $novelToc): int
{
    $lastupdt = $novelToc['subtitles'][$id]['download_time'];
    return $lastupdt;
}
