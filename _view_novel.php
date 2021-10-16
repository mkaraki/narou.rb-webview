<?php
require_once '_yamlmetadataloader.php';
require_once '_funcs.php';
$nid = $_GET['nid'];
$toc = loadToc($nid);

?>
<div>
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
    <table data-toggle="table" class="table table-striped <?= $_COOKIE['gcolorset'] === '1' ?  'table-dark' : ''; ?>">
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
                if (!empty($content['chapter']))
                    print('<tr><td colspan="3"><b>' . $content['chapter'] . '</b></td></tr>');
                if (!empty($content['subchapter']))
                    print('<tr><td colspan="3">' . $content['subchapter'] . '</td></tr>');
                print('<tr>');
                print(generateTdHtml(generateATag("index.php?v=read&sid=$id&nid=$nid", htmlxss($content['subtitle']), 'id="s-' . $id . '"')));
                print(generateTd($content['subdate']));
                print(generateTd($content['subupdate']));
                print('</tr>');
            }
            ?>
        </tbody>
    </table>
    <span><?= count($toc['subtitles']); ?>件の項目</span>
</div>

<script src="script.bookmark.js"></script>
<script src="script.novel.js"></script>
<script>
    window.onload = function() {
        bookmarkApply(<?= $nid; ?>);
    }
</script>