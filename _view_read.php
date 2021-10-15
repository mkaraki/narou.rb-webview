<?php
require_once '_yamlmetadataloader.php';
$ind = loadIndex();
$toc = loadToc($_GET['nid'], $ind);
$content = loadContent($_GET['sid'], $_GET['nid'], $toc, $ind);
?>
<div class="container">
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
            <?php
            if (isset($content['element']['introduction']))
                echo '<div class="novelview novelintro">' . $content['element']['introduction'] . '</div><hr />';
            ?>
            <div class="novelview body">
                <?= $content['element']['body'] ?>
            </div>
            <?php
            if (isset($content['element']['postscript']))
                echo '<hr /><div class="novelview novelpost">' . $content['element']['postscript'] . '</div>';
            ?>
        </div>
    </div>
</div>