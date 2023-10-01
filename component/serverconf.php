<?php
require_once __DIR__ . '/../__config.php';
require_once __DIR__ . '/../internal/funcs.php';
?>
<h5>サーバ側設定</h5>
<p>サーバ側設定は閲覧専用です。</p>
<div class="input-group mb-3">
    <span class="input-group-text">Narou.rb Data Dir</span>
    <input type="text" class="form-control" readonly value="<?= htmlxss($naroudir ?? ''); ?>">
</div>
<div class="input-group mb-3">
    <div class="input-group-text">
        API Client&nbsp;
        <input class="form-check-input" type="checkbox" readonly <?php if ($apiclient === true) print('checked="checked"'); ?>>
    </div>
    <input type="text" class="form-control" readonly value="<?= htmlxss($apiendpoint ?? ''); ?>">
</div>