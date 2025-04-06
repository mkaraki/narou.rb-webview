<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . '/postconfig.php';

define('APCU_AVAILABLE', function_exists('apcu_enabled') && apcu_enabled());

function loadYamlFromOs(string $path): array
{
    $spanContext = \Sentry\Tracing\SpanContext::make()
        ->setOp('yaml.load.os')
        ->setDescription('load: ' . $path);

    return \Sentry\trace(function () use ($path) {
        if (APCU_AVAILABLE && !isset($_GET['no_cache']) && apcu_exists($path)) {
            return apcu_fetch($path);
        }

        $data = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($path));

        if (APCU_AVAILABLE) {
            apcu_store($path, $data, 1800);
        }

        return $data;

    }, $spanContext);
}

function loadYamlFromGit(string $path, string $commit_id): array
{
    if (!preg_match('/^[0-9a-f]+$/i', $commit_id)) {
        die('不正なコミットIDです。');
    }

    $spanContext = \Sentry\Tracing\SpanContext::make()
        ->setOp('yaml.load.git')
        ->setDescription('load: ' . $path . ' commit: ' . $commit_id);

    return \Sentry\trace(function () use ($path, $commit_id) {
        if (str_starts_with($path, NAROU_DIR)) {
            $path = substr($path, strlen(NAROU_DIR) + 1);
        }
        $query = escapeshellarg($commit_id . ':' . $path);
        $git_dir = escapeshellarg(NAROU_DIR);

        if (APCU_AVAILABLE && !isset($_GET['no_cache']) && apcu_exists($path)) {
            return apcu_fetch($path);
        }

        $yaml = shell_exec('git -C ' . $git_dir . ' --no-pager show ' . $query);

        if ($yaml === null || $yaml === false) {
            die('Gitからの取得に失敗しました。');
        }

        $data = \Symfony\Component\Yaml\Yaml::parse($yaml);

        if (APCU_AVAILABLE) {
            apcu_store($path, $data, 1800);
        }

        return $data;
    }, $spanContext);
}

function loadYaml(string $path, string|null $commit_id = null): array
{
    if ($commit_id === null) {
        return loadYamlFromOs($path);
    } else {
        return loadYamlFromGit($path, $commit_id);
    }
}

function loadIndex(bool $useapi = false, string|null $commit_id = null): array
{
    return loadYaml(NAROU_DB_YAML, $commit_id);
}

function getIndexFileUpdateEpoch(): int
{
    return filemtime(NAROU_DB_YAML);
}

function loadIndexNovel(int $id, array $loadedindex = null, string|null $commit_id = null): array
{
    if ($loadedindex === null) $loadedindex = loadIndex(commit_id: $commit_id);

    if ($id >= count($loadedindex))
        die('該当の小説が見つかりませんでした。');

    return $loadedindex[$id];
}

function getTocPath(int $id, array $loadedindex = null, array $indexdata = null, string|null $commit_id = null): string
{
    if ($indexdata === null) $indexdata = loadIndexNovel($id, $loadedindex, commit_id: $commit_id);

    $tocpath = NAROU_NOVEL_DIR . DIRECTORY_SEPARATOR . $indexdata['sitename'] . DIRECTORY_SEPARATOR . $indexdata['file_title'] . DIRECTORY_SEPARATOR . 'toc.yaml';
    return $tocpath;
}

function loadToc(int $id, array $loadedindex = null, array $indexdata = null, string|null $commit_id = null): array
{
    $tocpath = getTocPath($id, $loadedindex, $indexdata, $commit_id);
    return loadYaml($tocpath, $commit_id);
}

function getContentPath(int $storyid, int $novelid, array $loadedtoc = null, array $loadedindex = null, string|null $commit_id = null): string
{
    if ($loadedindex === null) $loadedindex = loadIndex(commit_id: $commit_id);
    if ($loadedtoc === null) $loadedtoc = loadToc($novelid, $loadedindex, commit_id: $commit_id);

    if ($storyid >= count($loadedtoc['subtitles']))
        die('該当の話が見つかりませんでした。');

    $novelcontentdir  = NAROU_NOVEL_DIR . DIRECTORY_SEPARATOR . $loadedindex[$novelid]['sitename'] . DIRECTORY_SEPARATOR . $loadedindex[$novelid]['file_title'] . DIRECTORY_SEPARATOR . '本文';
    $novelcontentpath = $novelcontentdir . DIRECTORY_SEPARATOR . $loadedtoc['subtitles'][$storyid]['index'] . ' ' . $loadedtoc['subtitles'][$storyid]['file_subtitle'] . '.yaml';
    return $novelcontentpath;
}

function loadContent(int $storyid, int $novelid, array $loadedtoc = null, array $loadedindex = null, string|null $commit_id = null): array
{
    $novelcontentpath = getContentPath($storyid, $novelid, $loadedtoc, $loadedindex, $commit_id);
    return loadYaml($novelcontentpath, $commit_id);
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
