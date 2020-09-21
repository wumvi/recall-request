<?php

use Wumvi\ReCallRequest\ReCallRequestDao;
use Wumvi\ReCallRequest\ReCallRequestService;
use Wumvi\Sqlite3Dao\DbManager;

include __DIR__ . '/../vendor/autoload.php';

$dbManager = new DbManager(__DIR__ . '/recall-request.sqlite', 'wal');
$recallRequestDao = new ReCallRequestDao($dbManager);
$recallRequestService = new ReCallRequestService($recallRequestDao);
// $recallRequestService->add('http://localhost:8837', 'POST', '');

$recallRequestService->reCall();