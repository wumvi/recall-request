<?php

use Wumvi\ReCallRequest\ReCallRequestDao;
use Wumvi\ReCallRequest\ReCallRequestService;
use Wumvi\MysqlDao\DbManager;

include __DIR__ . '/../vendor/autoload.php';

$dbManager = new DbManager('mysql://service:service@localhost:3317/recall_request');
$recallRequestDao = new ReCallRequestDao($dbManager, true);
$recallRequestService = new ReCallRequestService($recallRequestDao);
$recallRequestService->addRecord('test', 'http://localhost:8837', 'POST', '', 'content');

// $recallRequestService->reCall();