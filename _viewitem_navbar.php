<?php
require_once('_postconfig.php');

?>
<nav class="navbar navbar-expand-lg <?= $_COOKIE['gcolorset'] === '1' ?  'navbar-dark bg-dark' : 'navbar-light bg-light'; ?>" id="navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Narou.rb Web Viewer</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php?v=updated">更新一覧</a>
                </li>
                <?php
                if ($apiclient === true) require('_viewitem_apiclientmenu.php');
                ?>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?v=conf">設定</a>
                </li>
            </ul>
        </div>
    </div>
</nav>