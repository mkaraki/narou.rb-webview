<?php
require_once __DIR__ . '/../internal/postconfig.php';
?>
<?php if ($allow_manualupdate ?? false) print '<li><a class="dropdown-item" href="update.php">手動更新</a></li>'; ?>
<li><a class="dropdown-item" href="index.php?v=new">追加リクエスト</a></li>