<?php

include 'lib/jsondb/JSONDB.php';

const DB_DIR = __DIR__.DIRECTORY_SEPARATOR.'db';
const DB_TABLE_JOB = 'jobs.json';
const APP_TRIGGER_TIME = '06:00';

if (!file_exists(DB_DIR.DIRECTORY_SEPARATOR.DB_TABLE_JOB)) {
    file_put_contents(DB_DIR.DIRECTORY_SEPARATOR.DB_TABLE_JOB, '[]');
}

$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : 'anon';
$jsonDb = new JSONDB(DB_DIR);
