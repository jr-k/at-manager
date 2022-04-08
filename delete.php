<?php
    include_once 'core/init.php';

    $error = null;

    if (C_APP_PASSWORD_ENABLED) {
        if (!isset($_GET['password']) || $_GET['password'] != C_APP_PASSWORD_VALUE) {
            $error = 'bad_password';
        }
    }

    if (empty($error)) {
        $job = $jsonDb->select('*')->from(DB_TABLE_JOB)->where(['id' => $_GET['id']])->getOne();

        if (!empty($job)) {
            if (!empty($job['atId'])) {
                exec('atrm ' . $job['atId']);
            }

            $jsonDb->delete()
                ->from(DB_TABLE_JOB)
                ->where(['id' => $job['id']])
                ->trigger();
        }
    }

    Header('Location:index.php?'
        .(isset($_GET['view_history']) ? '&view_history' : '')
        .(!empty($error) ? '&error='.$error : '')
    );
?>