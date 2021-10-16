<?php
require_once '_yamlmetadataloader.php';
require_once '_funcs.php';
$index = loadIndex(true);

$sort = 'general_lastup';
$asc = 'desc';

function cmpindex(array $a, array $b): int
{
    global $sort, $asc;
    if ($a[$sort] == $b[$sort]) {
        return 0;
    }
    return ($a[$sort] < $b[$sort]) ? -1 : 1;
}

?>
<div>
    <table data-toggle="table" class="table table-striped <?= $_COOKIE['gcolorset'] === '1' ?  'table-dark' : ''; ?>">
        <thead>
            <tr>
                <th>タイトル</th>
                <th>更新日</th>
                <th>作者</th>
            </tr>
        </thead>
        <tbody>
            <?php
            usort($index, 'cmpindex');
            foreach ($index as $content) {
                if (!isset($_COOKIE["bm-" . $content['id']])) continue;
                print('<tr>');
                print(generateTdHtml(
                    generateATag('index.php?v=novel&nid=' . $content['id'], htmlxss($content['title']))
                ));
                print(generateTd(date('Y/m/d H:i:s', $content['general_lastup'])));
                print(generateTd($content['author']));
                print('</tr>');
            }
            ?>
        </tbody>
    </table>
</div>