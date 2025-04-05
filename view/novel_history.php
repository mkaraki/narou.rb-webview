<?php
require_once __DIR__ . '/../internal/yamlmetadataloader.php';
require_once __DIR__ . '/../internal/funcs.php';
require_once __DIR__ . '/../internal/gitutil.php';
$nid = intval($request->nid);

// Index file update time must be max of each novel/story last update
// Check it's true
$idx_update = getIndexFileUpdateEpoch();
$last_update = putLastModifiedAndEnd($idx_update);

$ind = loadIndex();
$indn = loadIndexNovel($nid, $ind);

// Toc written update time is always max of each stories
// Or download date
// ToDo: Check about novel description update
$novelupd = getLastModifiedNovelEpoch($indn);
putLastModifiedAndEnd($novelupd, $last_update);

$toc = loadToc($nid, $ind, $indn);
$git_find_path = getTocPath($nid, $ind, $indn);

$history = getFileLogs(NAROU_DIR, $git_find_path);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/style.css">
    <title><?= htmlxss($toc['title'] ?? '無題の小説') ?> - ログ - Narou.rb Web Viewer</title>
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
            <th>コミットID</th>
            <th>コミット名</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($history as $commit) : ?>
        <tr>
            <th>
                <a href="/novel/<?= $nid ?>?commit_id=<?= $commit['commit_id'] ?>">
                    <?= htmlxss($commit['commit_id']) ?>
                </a>
            </th>
            <td><?= htmlxss($commit['message']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <span><?= count($history); ?>件の項目</span>
</main>


<script src="/assets/bootstrap.bundle.min.js"></script>
<script src="/assets/bootstrap.color.js"></script>
<script src="/assets/script.js"></script>
<script>
    applyGeneralSettings();
</script>
</body>

</html>