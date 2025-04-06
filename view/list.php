<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../internal/yamlmetadataloader.php';
require_once __DIR__ . '/../internal/funcs.php';

putLastModifiedAndEnd(getIndexFileUpdateEpoch());

$commit_id = null;
if (!empty($_GET['commit_id']) && preg_match('/^[0-9a-f]{7,40}$/', $_GET['commit_id'])) {
    $commit_id = $_GET['commit_id'];
}

$index = loadIndex(commit_id: $commit_id);

define('SORT', $_GET['s'] ?? 'title');
define('ASC', ($_GET['sd'] ?? 'asc') !== 'desc');

function cmpindex(array $a, array $b): int
{
    if ($a[SORT] == $b[SORT]) {
        return 0;
    }
    return ($a[SORT] < $b[SORT]) ? (ASC ? -1 : 1) : (ASC ? 1 : -1);
}

usort($index, 'cmpindex');

$count = count($index);
$skip = 0;
define('ITEM_IN_PAGE', 100);
if (!empty($_GET['skip']) && is_numeric($_GET['skip'])) {
    $skip = intval($_GET['skip']);
}

$offset = $skip * ITEM_IN_PAGE;
$limit_offset = $offset + ITEM_IN_PAGE;

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/style.css">
    <title>Narou.rb Web Viewer</title>
</head>

<body>
    <?php require __DIR__ . '/../component/navbar.php'; ?>
    <main>
        <table data-toggle="table" class="table table-striped">
            <thead>
                <tr>
                    <th>タイトル</th>
                    <th>更新日</th>
                    <th>作者</th>
                    <th>掲載サイト</th>
                </tr>
            </thead>
            <tbody>
                <?php for($i = $offset; ($i < $limit_offset && $i < $count); $i++) :
                    $content = $index[$i];
                ?>
                    <tr>
                        <?php $nid = (int)$content['id']; ?>
                        <td><a href="/novel/<?= $nid ?>?commit_id=<?= $commit_id ?>"><?= htmlxss($content['title']) ?></a></td>
                        <td><?= date('Y/m/d H:i:s', $content['general_lastup']) ?></td>
                        <td><?= htmlxss($content['author']) ?></td>
                        <td><a href="<?= $content['toc_url'] ?>" target="_blank"><?= htmlxss($content['sitename']) ?></a></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        <div>
            <?php if ($skip > 0) : ?>
                <a href="/?s=<?= urlencode(SORT) ?>&sd=<?= ASC ? 'asc' : 'desc' ?>&skip=<?= $skip - 1 ?>&commit_id=<?= $commit_id ?>">前ページ</a>
            <?php endif; ?>
            <?php if (($skip + 1) * ITEM_IN_PAGE < $count) : ?>
                <a href="/?s=<?= urlencode(SORT) ?>&sd=<?= ASC ? 'asc' : 'desc' ?>&skip=<?= $skip + 1 ?>&commit_id=<?= $commit_id ?>">次ページ</a>
            <?php endif; ?>
        </div>
        <span>全<?= $count; ?>件の項目 (<?= $skip * ITEM_IN_PAGE + 1 ?>件目から<?= ITEM_IN_PAGE ?>件を表示中)</span>
    </main>

    <script src="/assets/bootstrap.bundle.min.js"></script>
    <script src="/assets/bootstrap.color.js"></script>
    <script src="/assets/script.js"></script>
    <script>
        applyGeneralSettings();
    </script>
</body>

</html>