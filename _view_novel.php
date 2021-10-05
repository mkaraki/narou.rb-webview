<?php
require_once '_yamlmetadataloader.php';
require_once '_funcs.php';
$nid = $_GET['nid'];
$toc = loadToc($nid);

?>
<div>
    <table data-toggle="table" class="table table-striped">
        <thead>
            <tr>
                <th>タイトル</th>
                <th>掲載日</th>
                <th>更新日</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($toc['subtitles'] as $id => $content) {
                print('<tr>');
                print(generateTdHtml('<a href="index.php?v=read&sid=' . $id . '&nid=' . $nid . '">' . htmlxss($content['subtitle']) . '</a>', ''));
                print(generateTd($content['subdate']));
                print(generateTd($content['subupdate']));
                print('</tr>');
            }
            ?>
        </tbody>
    </table>
    <span><?= count($toc['subtitles']); ?>件の項目</span>
</div>