<?php
global $router;

$router->respond('GET', '/intra/update-list', function () {
    require_once __DIR__ . '/internal/yamlmetadataloader.php';

    $index = loadIndex();
    $index2 = [];

    if (empty($_GET['freq'])) {
        return '';
    }

    switch($_GET['freq']) {
        case 'daily':
            // Update more than this
            $target_last_update = time() - (60 * 60 * 24 * 3);
            // Ignore more than this
            $ignore_last_update = time();
            break;

        case 'weekly':
            $target_last_update = time() - (60 * 60 * 24 * 30);
            $ignore_last_update = time() - (60 * 60 * 24 * 3);
            break;

        case 'monthly':
            $target_last_update = time() - (60 * 60 * 24 * 30 * 2);
            $ignore_last_update = time() - (60 * 60 * 24 * 30);
            break;

        case '2month':
            $target_last_update = time() - (60 * 60 * 24 * 30 * 6);
            $ignore_last_update = time() - (60 * 60 * 24 * 30 * 2);
            break;

        case '4month':
            $target_last_update = time() - (60 * 60 * 24 * 365);
            $ignore_last_update = time() - (60 * 60 * 24 * 30 * 6);
            break;

        case '6month':
            $target_last_update = 0;
            $ignore_last_update = time() - (60 * 60 * 24 * 365);
            break;

        default:
            return '';
    }

    $ignore_last_check = time() - (60 * 60 * 23);

    for ($i = 0; $i < count($index); $i++) {
        if (($index[$i]['last_check_date'] ?? 0) > $ignore_last_check) {
            continue;
        }

        if (
            ($index[$i]['last_update'] ?? 0) > $target_last_update ||
            ($index[$i]['general_lastup'] ?? 0) > $target_last_update
        ) {
            $index2[] = $index[$i];
        }
    }

    $index = [];
    for ($i = 0; $i < count($index2); $i++) {
        if (
            ($index2[$i]['last_update'] ?? 0) <= $ignore_last_update ||
            ($index2[$i]['general_lastup'] ?? 0) <= $ignore_last_update
        ) {
            $index[] = $index2[$i];
        }
    }

    foreach ($index as $i) {
        print($i['id'] . "\n");
    }

    return '';
});
