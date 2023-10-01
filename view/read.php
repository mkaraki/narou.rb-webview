<?php
require_once __DIR__ . '/../internal/yamlmetadataloader.php';
require_once __DIR__ . '/../internal/funcs.php';
$sid = intval($request->sid);
$nid = intval($request->nid);

$ind = loadIndex();
$toc = loadToc($nid, $ind);
$content = loadContent($sid, $nid, $toc, $ind);

$prevcode = $sid - 1;
$nextcode = $sid + 1;
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/style.css">
    <title><?= htmlxss($content['subtitle'] ?? '無題のストーリ') ?> - <?= htmlxss($toc['title'] ?? '無題の小説') ?> - Narou.rb Web Viewer</title>
</head>

<body>
    <?php require __DIR__ . '/../component/navbar.php'; ?>
    <div class="container p-4 read-container">
        <div class="row">
            <div class="col">
                <div class="text-center">
                    <h3><?= htmlxss($toc['title'] ?? '無題の小説') ?></h3>
                    <h5><?= htmlxss($content['chapter'] ?? '') ?></h5>
                    <h5><?= htmlxss($content['subchapter'] ?? '') ?></h5>
                    <h5><?= htmlxss($content['subtitle'] ?? '') ?></h5>
                </div>
                <div class="text-end">
                    <?= htmlxss($toc['author'] ?? ''); ?>
                </div>
            </div>
            <hr />
        </div>
        <?php require __DIR__ . '/../component/novel_jump_menu.php'; ?>
        <div class="row">
            <div class="col">
                <div class="novelview novelintro"><?= $content['element']['introduction'] ?></div>
                <hr />
                <div class="novelview body">
                    <?= $content['element']['body'] ?>
                </div>
                <hr />
                <div class="novelview novelpost"><?= $content['element']['postscript'] ?></div>
            </div>
        </div>
        <?php require __DIR__ . '/../component/novel_jump_menu.php'; ?>
    </div>

    <script src="/assets/bootstrap.bundle.min.js"></script>
    <script src="/assets/bootstrap.color.js"></script>
    <script src="/assets/script.js"></script>
    <script>
        applyGeneralSettings();
    </script>
</body>

</html>