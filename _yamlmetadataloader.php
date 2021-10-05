<?php
require_once("vendor/autoload.php");
require_once('_postconfig.php');

function loadYaml($path)
{
    return \Symfony\Component\Yaml\Yaml::parse(file_get_contents($path));
}

function loadIndex()
{
    global $naroudbyaml;
    return loadYaml($naroudbyaml);
}

function loadToc($id, $loadedindex = null)
{
    global $narounoveldir;
    if ($loadedindex === null) $loadedindex = loadIndex();

    $indexdata = $loadedindex[$id];
    $tocpath = $narounoveldir . DIRECTORY_SEPARATOR . $indexdata['sitename'] . DIRECTORY_SEPARATOR . $indexdata['file_title'] . DIRECTORY_SEPARATOR . 'toc.yaml';
    return loadYaml($tocpath);
}

function loadContent($storyid, $novelid, $loadedtoc = null, $loadedindex = null)
{
    global $narounoveldir;
    if ($loadedindex === null) $loadedindex = loadIndex();
    if ($loadedtoc === null) $loadedtoc = loadToc($novelid, $loadedindex);

    $novelcontentdir  = $narounoveldir . DIRECTORY_SEPARATOR . $loadedindex[$novelid]['sitename'] . DIRECTORY_SEPARATOR . $loadedindex[$novelid]['file_title'] . DIRECTORY_SEPARATOR . '本文';
    $novelcontentpath = $novelcontentdir . DIRECTORY_SEPARATOR . $loadedtoc['subtitles'][$storyid]['index'] . ' ' . $loadedtoc['subtitles'][$storyid]['file_subtitle'] . '.yaml';
    return loadYaml($novelcontentpath);
}
