<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>小説の追加リクエスト</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.1/css/bootstrap.min.css" integrity="sha512-6KY5s6UI5J7SVYuZB4S/CZMyPylqyyNZco376NM2Z8Sb8OxEdp02e1jkKk/wZxIEmjQ6DRCEBhni+gpr9c4tvA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <?php
    require('_viewitem_navbar.php');

    if (!isset($_POST['url'])) die('不正なリクエストです。');
    $turl = $_POST['url'];

    printf('リクエストを受け付けました。');
    ?>
</body>

</html>
<?php
$req = http_build_query(array(
    "targets" => $turl,
), "", "&");

file_get_contents($apiclient_add_url, false, stream_context_create(array(
    "http" => array(
        "method"  => "POST",
        "header" => implode("\r\n", array(
            "Content-Type: application/x-www-form-urlencoded",
            "Content-Length: " . strlen($req)
        )),
        "content" => $req
    )
)));
?>