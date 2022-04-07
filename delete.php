<?php

    include_once 'config.php';

    $jsonDb->delete()
        ->from( DB_TABLE_JOB )
        ->where( [ 'id' => $_GET['id'] ] )
        ->trigger();

    Header('Location:index.php');