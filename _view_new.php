<?php
require_once('_postconfig.php');

?>
<div class="container">
    <div class="row">
        <div class="col">
            <form action="add.php" method="post">
                <div class="mb-3">
                    <label for="novelurl" class="form-label">小説のURL</label>
                    <input type="url" class="form-control" id="novelurl" name="url">
                </div>
                <button type="submit" class="btn btn-primary">ダウンロード</button>
            </form>
        </div>
    </div>
</div>