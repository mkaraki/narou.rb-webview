<?php
require_once __DIR__ . '/../internal/yamlmetadataloader.php';
require_once __DIR__ . '/../internal/funcs.php';
$nid = intval($request->nid);

$commit_id = null;
if (!empty($_GET['commit_id']) && preg_match('/^[0-9a-f]{7,40}$/', $_GET['commit_id'])) {
    $commit_id = $_GET['commit_id'];
}

// Index file update time must be max of each novel/story last update
// Check it's true
$idx_update = getIndexFileUpdateEpoch();
$last_update = putLastModifiedAndEnd($idx_update);

$ind = loadIndex(commit_id: $commit_id);
$indn = loadIndexNovel($nid, $ind, $commit_id);

// Toc written update time is always max of each stories
// Or download date
// ToDo: Check about novel description update
$novelupd = getLastModifiedNovelEpoch($indn);
putLastModifiedAndEnd($novelupd, $last_update);

$toc = loadToc($nid, $ind, $indn, $commit_id);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/style.css">
    <title><?= htmlxss($toc['title'] ?? '無題の小説') ?> - Narou.rb Web Viewer</title>
</head>

<body>
    <?php require __DIR__ . '/../component/navbar.php'; ?>
    <main>
        <div class="metadatas">
            <div class="container p-4">
                <div class="row">
                    <div class="col">
                        <div class="text-center">
                            <h2><?= $toc['title']; ?></h2>
                        </div>
                        <div class="text-end">
                            <?= $toc['author']; ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <p><?= str_replace("\n", "<br />", $toc['story']); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <table data-toggle="table" class="table table-striped">
            <thead>
                <tr>
                    <th>タイトル</th>
                    <th>掲載日</th>
                    <th>更新日</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($toc['subtitles'] as $id => $content) : ?>
                    <?php if (!empty($content['chapter'])) : ?>
                        <tr>
                            <td colspan="3"><b><?= htmlxss($content['chapter']) ?></b></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($content['subchapter'])) : ?>
                        <tr>
                            <td colspan="3"><?= htmlxss($content['subchapter']) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td><a href="/novel/<?= $nid ?>/<?= $id ?>?commit_id=<?= $commit_id ?>" id="s-<?= $id ?>"><?= htmlxss($content['subtitle']) ?></a></td>
                        <td><?= htmlxss($content['subdate']) ?></td>
                        <td><?= htmlxss($content['subupdate']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <span><?= count($toc['subtitles']); ?>件の項目</span>
        <a href="/novel/<?= $nid ?>/history">過去ログを見る</a>
    </main>


    <script src="/assets/bootstrap.bundle.min.js"></script>
    <script src="/assets/bootstrap.color.js"></script>
    <script src="/assets/script.js"></script>
    <script>
        applyGeneralSettings();
    </script>
</body>

</html>