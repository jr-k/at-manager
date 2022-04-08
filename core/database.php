<?php

include_once 'lib/jsondb/JSONDB.php';

const DB_DIR = 'database';
const DB_TABLE_JOB = 'jobs.json';

if (!file_exists(DB_DIR.DIRECTORY_SEPARATOR.DB_TABLE_JOB)) {
    file_put_contents(DB_DIR.DIRECTORY_SEPARATOR.DB_TABLE_JOB, '[]');
}

$jsonDb = new JSONDB(DB_DIR);

?>