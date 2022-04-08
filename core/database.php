<?php

include_once 'lib/jsondb/JSONDB.php';

if (!file_exists(DIR_DB.DIRECTORY_SEPARATOR.DB_TABLE_JOB)) {
    file_put_contents(DIR_DB.DIRECTORY_SEPARATOR.DB_TABLE_JOB, '[]');
}

$jsonDb = new JSONDB(DIR_DB);

?>