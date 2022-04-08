<?php

if (!file_exists('config.php')) {
    die('You\'ve to copy config.php.dist into config.php');
}

include_once 'constants.php';
include_once 'config.php';
include_once 'auth.php';
include_once 'database.php';

?>