<div class="row">
    <div class="col-4 text-center">
        <?php if ($prevcode >= 0) : ?>
            <a href="/novel/<?= $nid ?>/<?= $prevcode ?>">前へ</a>
        <?php endif; ?>
    </div>
    <div class="col-4 text-center">
        <a href="/novel/<?= $nid ?>">目次</a>
    </div>
    <div class="col-4 text-center">
        <?php if ($nextcode < count($toc['subtitles'])) : ?>
            <a href="/novel/<?= $nid ?>/<?= $nextcode ?>">次へ</a>
        <?php endif; ?>
    </div>
</div>