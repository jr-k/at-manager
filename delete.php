<?php
    include_once 'core/init.php';

    $job = $jsonDb->select('*')->from(DB_TABLE_JOB)->where(['id' => $_GET['id']])->getOne();

    if (!empty($job)) {
        if (!empty($job['atId'])) {
            exec('atrm ' . $job['atId']);
        }

        $jsonDb->delete()
            ->from( DB_TABLE_JOB )
            ->where( [ 'id' => $job['id'] ] )
            ->trigger();
    }

    Header('Location:index.php?'.(isset($_GET['view_history']) ? 'view_history' : ''));
?>