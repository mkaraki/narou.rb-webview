<?php
require_once '_yamlmetadataloader.php';
require_once '_funcs.php';
$index = loadIndex(true);

$sort = $_GET['s'] ?? 'title';
$asc = ($_GET['sd'] ?? 'asc') !== 'desc';

function cmpindex(array $a, array $b): int
{
    global $sort, $asc;
    if ($a[$sort] == $b[$sort]) {
        return 0;
    }
    return ($a[$sort] < $b[$sort]) ? ($asc ? -1 : 1) : ($asc ? 1 : -1);
}

function generateNewbadge(int $nid, int $tno): string
{
    if (!isset($_COOKIE["bm-$nid"])) return '';
    $bsid = intval($_COOKIE["bm-$nid"]);

    if ($bsid + 1 < $tno) return '<span class="badge bg-primary">New</span>';
    else return '';
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
                $nid = (int)$content;
                print('<tr>');
                print(generateTdHtml(
                    generateATag("index.php?v=novel&nid=$nid", htmlxss($content['title'])) .
                        generateNewbadge($nid, $content['general_all_no'])
                ));
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