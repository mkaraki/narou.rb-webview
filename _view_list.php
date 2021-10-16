<?php
require_once '_yamlmetadataloader.php';
require_once '_funcs.php';
$index = loadIndex(true);

$sort = $_GET['s'] ?? 'title';
$asc = ($_GET['sd'] ?? 'asc') !== 'desc';

function cmpindex($a, $b)
{
    global $sort, $asc;
    if ($a[$sort] == $b[$sort]) {
        return 0;
    }
    return ($a[$sort] < $b[$sort]) ? ($asc ? -1 : 1) : ($asc ? 1 : -1);
}

?>
<div>
    <table data-toggle="table" class="table table-striped <?= $_COOKIE['gcolorset'] === '1' ?  'table-dark' : ''; ?>">
        <thead>
            <tr>
                <th>タイトル</th>
                <th>更新日</th>
                <th>作者</th>
                <th>掲載サイト</th>
            </tr>
        </thead>
        <tbody>
            <?php
            usort($index, 'cmpindex');
            foreach ($index as $content) {
                print('<tr>');
                print(generateTdHtml(generateATag('index.php?v=novel&nid=' . $content['id'], htmlxss($content['title']))));
                print(generateTd(date('Y/m/d H:i:s', $content['general_lastup'])));
                print(generateTd($content['author']));
                print(generateTdHtml(generateATag($content['toc_url'], htmlxss($content['sitename']))));
                print('</tr>');
            }
            ?>
        </tbody>
    </table>
    <span><?= count($index); ?>件の項目</span>
</div>