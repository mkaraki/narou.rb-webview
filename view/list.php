<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../internal/yamlmetadataloader.php';
require_once __DIR__ . '/../internal/funcs.php';

$index = loadIndex(true);

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
                <?php foreach ($index as $content) : ?>
                    <tr>
                        <?php $nid = (int)$content['id']; ?>
                        <td><a href="/novel/<?= $nid ?>"><?= htmlxss($content['title']) ?></a></td>
                        <td><?= date('Y/m/d H:i:s', $content['general_lastup']) ?></td>
                        <td><?= htmlxss($content['author']) ?></td>
                        <td><a href="<?= $content['toc_url'] ?>" target="_blank">htmlxss($content['sitename'])</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <span><?= count($index); ?>件の項目</span>
    </main>

    <script src="/assets/bootstrap.bundle.min.js"></script>
    <script src="/assets/bootstrap.color.js"></script>
    <script src="/assets/script.js"></script>
    <script>
        applyGeneralSettings();
    </script>
</body>

</html>