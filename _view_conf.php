<?php
require_once '_postconfig.php';
?>
<div id="confscr" class="d-none">
    <div class="alert alert-warning" role="alert">
        設定値としおりのデータはブラウザ依存です。データを他のブラウザに移行したい場合、<a class="alert-link" href="#transferSettings">エクスポート機能</a>を利用してください。
        <br />
        データの有効期限は<a class="alert-link" href="#cookieExpire">クッキー有効期限</a>で設定できます。ブラウザのクッキーを削除するとデータが失われます。
    </div>

    <form action="#" onsubmit="saveConf(); return false;">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h3>一般画面設定</h3>
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="gcolorSelector">カラーテーマ</label>
                        <select class="form-select" id="gcolorSelector">
                            <option value="0">白</option>
                            <option value="1">黒</option>
                        </select>
                    </div>
                    <p>
                        この値は小説画面には適応されません。
                        小説画面の設定は<a href="#colorSelector">小説画面設定の表示色設定</a>から変更できます。
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>小説画面設定</h3>
                    <div class="input-group mb-3">
                        <span class="input-group-text">小説画面幅</span>
                        <input type="number" class="form-control" value="700" id="readerMaxWidth" min="100">
                        <span class="input-group-text">px</span>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="colorSelector">表示色</label>
                        <select class="form-select" id="colorSelector">
                            <option value="0">白背景</option>
                            <option value="1">黒背景</option>
                            <option value="2">暖色</option>
                        </select>
                    </div>
                    <p>
                        この値は小説画面以外には適応されません。
                        小説画面以外の設定は<a href="#gcolorSelector">一般画面設定のカラーテーマ設定</a>から変更できます。
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div>
                        <button type="submit" class="btn btn-primary mb-3">設定を保存</button>
                        <button class="btn btn-danger mb-3" onclick="location.reload(); return false;">設定を破棄</button>
                    </div>
                    <hr />
                </div>
            </div>
            <div class="row" id="transferSettings">
                <div class="col">
                    <h3>インポート・エクスポート</h3>
                    <div id="importexportMenu">
                        <button type="button" class="btn btn-primary" onclick="exportConf();">エクスポート</button>
                        <button type="button" class="btn btn-danger" onclick="importConf();">インポート</button>
                    </div>
                    <div id="importconf" class="d-none">
                        <div class="alert alert-danger" role="alert">
                            設定をインポートすると、現在の設定は上書きされます。
                        </div>
                        <div class="input-group">
                            <textarea class="form-control" id="iconfbox"></textarea>
                        </div>
                        <br />
                        <button type="button" class="btn btn-danger" onclick="doImportConf();">設定をインポートする</button>
                        <br />
                    </div>
                    <div id="exportconf" class="d-none">
                        <br />
                        <div class="input-group">
                            <textarea class="form-control" id="econfbox"></textarea>
                        </div>
                    </div>
                    <br />
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>高度な設定</h3>
                    <div class="input-group mb-3">
                        <span class="input-group-text">クッキー有効期限</span>
                        <input type="number" class="form-control" value="36500" id="cookieExpire">
                        <span class="input-group-text">日</span>
                    </div>
                    <p>
                        最後の設定保存からデータが失われるまでの日数です。<br />
                        <span class="text-danger">ブラウザのクッキーが失われた場合、データも同様に失われます。定期的にエクスポートを行い、バックアップを取ってください。</span>
                    </p>
                    <?php if ($allow_readserverconf ?? false) require('_viewitem_serverconf.php');  ?>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="script.conf.js"></script>
<script>
    document.getElementById('loadscr').classList.remove('d-none');
    window.addEventListener('load', (event) => {
        showConf();
    });
</script>