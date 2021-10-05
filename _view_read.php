<?php
require_once '_yamlmetadataloader.php';
$content = loadContent($_GET['sid'], $_GET['nid']);
?>
<div class="novelview">
    <?= $content['element']['body'] ?>
</div>