<?php
error_reporting(E_ALL & ~E_DEPRECATED);
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/internal/postconfig.php';

$sentryOpts = [
    'traces_sample_rate' => 1.0,
];

if (defined('SENTRY_DSN') && !empty(SENTRY_DSN)) {
    $sentryOpts['dsn'] = SENTRY_DSN;
}

\Sentry\init($sentryOpts);

$router = new \Klein\Klein();

$router->respond('GET', '/', function () {
    require __DIR__ . '/view/list.php';
});

$router->respond('GET', '/novel/[i:nid]', function ($request, $response, $service, $app) {
    require __DIR__ . '/view/novel.php';
});

$router->respond('GET', '/novel/[i:nid]/history', function ($request, $response, $service, $app) {
    require __DIR__ . '/view/novel_history.php';
});

$router->respond('GET', '/novel/[i:nid]/[i:sid]', function ($request, $response, $service, $app) {
    require __DIR__ . '/view/read.php';
});

$router->respond('GET', '/assets/style.css', function () {
    header('Content-Type: text/css');
    readfile(__DIR__ . '/assets/style.css');
});

$router->respond('GET', '/assets/script.js', function () {
    header('Content-Type: text/javascript');
    readfile(__DIR__ . '/assets/script.js');
});

$router->respond('GET', '/assets/bootstrap.min.css', function () {
    header('Content-Type: text/css');
    readfile(__DIR__ . '/vendor/twbs/bootstrap/dist/css/bootstrap.min.css');
});

$router->respond('GET', '/assets/bootstrap.bundle.min.js', function () {
    header('Content-Type: text/javascript');
    readfile(__DIR__ . '/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js');
});

$router->respond('GET', '/assets/bootstrap.color.js', function () {
    header('Content-Type: text/javascript');
    readfile(__DIR__ . '/assets/bootstrap.color.js');
});

require_once __DIR__ . '/intra_route.php';

$transactionContext = \Sentry\Tracing\TransactionContext::make()
    ->setName($_SERVER['REQUEST_URI'])
    ->setOp('http.server');
$transaction = \Sentry\startTransaction($transactionContext);
\Sentry\SentrySdk::getCurrentHub()->setSpan($transaction);

$router->dispatch();

$transaction->finish();
