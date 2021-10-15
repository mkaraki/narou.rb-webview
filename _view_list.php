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
            <?php
            usort($index, 'cmpindex');
            foreach ($index as $content) {
                print('<tr>');
                print(generateTdHtml('<a href="index.php?v=novel&nid=' . $content['id'] . '">' . htmlxss($content['title']) . '</a>', ''));
                print(generateTd(date('Y/m/d H:i:s', $content['novelupdated_at'] ?? $content['general_lastup'])));
                print(generateTd($content['author']));
                print(generateTdHtml('<a href="' . $content['toc_url'] . '">' . htmlxss($content['sitename']) . '</a>', ''));
                print('</tr>');
            }
            ?>
        </tbody>
    </table>
    <span><?= count($index); ?>件の項目</span>
</div>