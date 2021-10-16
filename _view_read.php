<?php
require_once '_yamlmetadataloader.php';
$sid = intval($_GET['sid']);
$nid = $_GET['nid'];

$ind = loadIndex();
$toc = loadToc($_GET['nid'], $ind);
$content = loadContent($sid, $_GET['nid'], $toc, $ind);

$prevcode = $sid - 1;
$nextcode = $sid + 1;
$prevurl = "index.php?v=read&sid=$prevcode&nid=$nid";
$nexturl = "index.php?v=read&sid=$nextcode&nid=$nid";
$novelurl = "index.php?v=novel&nid=$nid";
?>
<div class="container p-4" style="max-width: <?= $_COOKIE['readermaxwid'] ?? 700; ?>;">
    <div class="row">
        <div class="col">
            <div class="text-center">
                <h3><?= $toc['title']; ?></h3>
                <h5><?= $content['chapter']; ?></h5>
                <h5><?= $content['subchapter']; ?></h5>
                <h5><?= $content['subtitle']; ?></h5>
            </div>
            <div class="text-end">
                <?= $toc['author']; ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="novelview novelintro"><?= $content['element']['introduction'] ?></div>
            <hr />
            <div class="novelview body">
                <?= $content['element']['body'] ?>
            </div>
            <hr />
            <div class="novelview novelpost"><?= $content['element']['postscript'] ?></div>
        </div>
    </div>
    <div class="row">
        <div class="col-4 text-center">
            <?php
            if ($prevcode >= 0)
                print('<a href="' . $prevurl . '">前へ</a>');
            ?>
        </div>
        <div class="col-4 text-center">
            <a href="<?= $novelurl ?>">目次</a>
            <br />
            <a href="javascript:void(0)" onclick="bookmarkAdd(<?= $nid; ?>, <?= $sid ?>);" id="addBmark" aria-disabled="false">
                ページを保存
            </a>
        </div>
        <div class="col-4 text-center">
            <?php
            if ($nextcode < count($toc['subtitles']))
                print('<a href="' . $nexturl . '">次へ</a>');
            ?>
        </div>
    </div>
</div>
<script src="script.bookmark.js"></script>
<script src="script.reader.js"></script>
<script>
    window.onload = function() {
        bookmarkCheck(<?= $nid; ?>, <?= $sid; ?>, true);
    }
</script>