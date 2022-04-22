<?php

if (!file_exists('config.php')) {
    die('You\'ve to copy config.php.dist into config.php or execute \'make init\'');
}

include_once 'constants.php';

date_default_timezone_set(APP_TIMEZONE);

include_once 'config.php';
include_once 'auth.php';
include_once 'database.php';

?>
