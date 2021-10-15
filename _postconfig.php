<?php
require_once('__config.php');
$naroudbyaml = $naroudir . DIRECTORY_SEPARATOR  . '.narou' . DIRECTORY_SEPARATOR . 'database.yaml';
$narounoveldir = $naroudir . DIRECTORY_SEPARATOR  . '小説データ';

$apiendpoint = $apiendpoint ?? 'http://localhost:8000';
$apiclient_update_url = $apiendpoint . '/api/update';
$apiclient_add_url = $apiendpoint . '/api/download';
$apiclient_list_url = $apiendpoint . '/api/list';
