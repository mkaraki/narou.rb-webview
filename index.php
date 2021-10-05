<?php
$siteentry = $_GET['v'] ?? 'list';
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Narou.rb Web Viewer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.1/css/bootstrap.min.css" integrity="sha512-6KY5s6UI5J7SVYuZB4S/CZMyPylqyyNZco376NM2Z8Sb8OxEdp02e1jkKk/wZxIEmjQ6DRCEBhni+gpr9c4tvA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Narou.rb Web Viewer</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php?v=list">小説一覧</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php?v=list&s=novelupdated_at&sd=desc">更新一覧</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php
    require('_view_' . $siteentry . '.php');
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.1/js/bootstrap.min.js" integrity="sha512-ewfXo9Gq53e1q1+WDTjaHAGZ8UvCWq0eXONhwDuIoaH8xz2r96uoAYaQCm1oQhnBfRXrvJztNXFsTloJfgbL5Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/@riversun/sortable-table/lib/sortable-table.js"></script>
</body>

</html>