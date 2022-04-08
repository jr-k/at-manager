<?php

    include_once 'core/init.php';

    $now = new \DateTime();

    $jobsIndex = [];
    $jobsRows = $jsonDb->select('*')->from(DB_TABLE_JOB)->order_by('date')->get();
    $errors = [];
    $successes = [];

    if (isset($_GET['error'])) {
        if ($_GET['error'] === 'bad_password') {
            $errors[] = 'Bad password';
        }
    }

    function jKey($jobItem) {
        return $jobItem['date'].';'.$jobItem['time'].';'.$jobItem['job'];
    }

    foreach($jobsRows as $row) {
        $jobsIndex[jKey($row)] = $row;
    }

    $formTriggered = isset($_POST['jobs']);

    $jobTime = C_APP_CUSTOM_TIME_ENABLED && isset($_POST['time']) ? $_POST['time'] : C_APP_DEFAULT_TRIGGER_TIME;

    if ($formTriggered && C_APP_PASSWORD_ENABLED) {
        if (!isset($_POST['password']) || $_POST['password'] != C_APP_PASSWORD_VALUE) {
            $errors[] = 'Bad password';
        }
    }

    if (!$formTriggered && !empty($_POST) && empty($errors)) {
        $errors[] = 'You must select at least one job';
    }

    if ($formTriggered && empty($errors) && isset($_POST['date'])) {
        $jobDate = \DateTime::createFromFormat('Y-m-d H:i:s', $_POST['date'].' '.$jobTime.':00');

        if ($jobDate === false) {
            $errors[] = sprintf('Bad date format');
        } elseif ((int)$now->format('U') >= (int)$jobDate->format('U')) {
            $errors[] = 'Date is in the past, you must select a future date';
        }
    }

    if ($formTriggered && empty($errors)) {
        foreach($_POST['jobs'] as $job) {
            $m = microtime(true);
            $id = sprintf("%8x%05x",floor($m),($m-floor($m))*1000000);
            $jobDate = \DateTime::createFromFormat('Y-m-d H:i:s', $_POST['date'].' '.$jobTime.':00');

            $newJob = [
                'atId' => '',
                'id' => $id,
                'job' => $job,
                'date' => $jobDate->format('Y-m-d'),
                'time' => $jobDate->format('H:i'),
                'comment' => $_POST['comment'],
                'appUser' => $user,
                'sysUser' => '',
            ];

            if (array_key_exists(jKey($newJob), $jobsIndex)) {
                $errors[] = sprintf('A push for %s is already planned !', $job);
                continue;
            }

            $sysUserOutput = null;
            exec('whoami', $sysUserOutput);
            $newJob['sysUser'] = $sysUserOutput[0];

            $atOutput = null;
            $cmd = sprintf('at %s %s -f %s 2>&1', $jobDate->format('H:i'), $jobDate->format('Y-m-d'), realpath($job));
            exec($cmd, $atOutput);

            $match = null;

            foreach($atOutput as $output) {
                if (preg_match('#job ([0-9]+) at(.+)#', $output, $match)) {
                    if (isset($match[1])) {
                        $newJob['atId'] = $match[1];
                        break;
                    }
                }
            }

            $jsonDb->insert(DB_TABLE_JOB, $newJob);
            $jobsRows[] = $newJob;
            $jobsIndex[jKey($newJob)] = $newJob;
            $successes[] = sprintf('A push for %s has been successfully planned !', $job);
        }
    }

?>

<html>
    <head>
        <meta charset="UTF-8" />
        <title><?php echo C_APP_TITLE; ?></title>
        <link rel="shortcut icon" href="favicon.ico">
        <link href="css/bootstrap.min.css" rel="stylesheet" />
        <link href="css/bootstrap-theme.min.css" rel="stylesheet" />
        <link href="css/jquery-ui.min.css" rel="stylesheet" />
        <link href="css/jquery.timepicker.min.css" rel="stylesheet" />
    </head>
    <body>
        <?php
            $availableJobScripts = [];

            foreach (scandir(DIR_JOBS) as $file) {
                if ($file[0] === '.') continue;
                $name = str_replace('.sh', '', $file);

                $availableJobScripts[] = [
                    'name' => $name,
                    'label' => ucfirst($name),
                    'file' => DIR_JOBS.DIRECTORY_SEPARATOR.$file
                ];
            }
        ?>


        <div class="container">

            <h1><strong><?php echo C_APP_TITLE; ?></strong></h1>
            <br />

            <div class="row jumbotron" style="padding: 20px;">

                <?php if (!empty($errors)) { ?>
                    <div class="col-lg-12">
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach($errors as $error) { ?>
                                    <li><strong><?php echo $error; ?></strong></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                <?php } ?>

                <?php if (!empty($successes)) { ?>
                    <div class="col-lg-12">
                        <div class="alert alert-success">
                            <ul>
                                <?php foreach($successes as $success) { ?>
                                    <li><strong><?php echo $success; ?></strong></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                <?php } ?>

                <form method="post" class="">

                    <div class="col-lg-2">
                        <div class="form-group">
                            <div style="display: flex; flex-direction: column">
                                <label for="">
                                    Scripts
                                </label>
                                <?php foreach($availableJobScripts as $job) { ?>
                                    <div style="display: flex; flex-direction: row">
                                        <input id="job_<?php echo $job['name']; ?>" type="checkbox" name="jobs[]" value="<?php echo $job['file']; ?>" />
                                        <label style="margin-left: 5px; font-weight: normal" for="job_<?php echo $job['name']; ?>"><?php echo $job['label'] ?></label>
                                    </div>
                                <?php } ?>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Comment</label>
                            <input type="text" name="comment" class="form-control" autocomplete="off" />
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="text" name="date" class="datepicker form-control" autocomplete="off" />
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Time</label>
                            <input type="text" name="time" class="timepicker form-control" autocomplete="off" value="<?php echo C_APP_DEFAULT_TRIGGER_TIME; ?>" <?php if (!C_APP_CUSTOM_TIME_ENABLED) { ?>disabled="disabled"<?php } ?> />
                        </div>
                    </div>

                    <?php if (C_APP_PASSWORD_ENABLED) { ?>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" autocomplete="off" />
                            </div>
                        </div>
                    <?php } ?>

                    <button type="submit" class="btn btn-success" style="margin-top:24px;margin-left:24px;">
                        Add new job
                    </button>
                </form>
            </div>



            <hr>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>AT id</th>
                        <th>App User</th>
                        <th>Sys User</th>
                        <th>Script</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $jobTotal = 0;
                        $jobScheduled = 0;

                        foreach ($jobsRows as $row) {
                            $jobDate = \DateTime::createFromFormat('Y-m-d H:i', $row['date'].' '.$row['time']);
                            $jobDateDiff = (int) $now->format('U') - (int) $jobDate->format('U');
                            $pastJob = $jobDateDiff >= 0;
                            $jobTotal++;

                            if ($pastJob && !isset($_GET['view_history'])) continue;

                            $jobScheduled++;
                    ?>
                    <tr>
                        <td>
                            <div class="badge" style="font-size: 10px;">
                                <?php echo isset($row['id']) ? $row['id'] : 'N/A'; ?>
                            </div>
                        </td>
                        <td>
                            <div class="badge" style="font-size: 10px;">
                                <?php echo isset($row['atId']) ? $row['atId'] : 'N/A'; ?>
                            </div>
                        </td>
                        <td>
                            <?php echo isset($row['appUser']) ? $row['appUser'] : 'N/A'; ?>
                        </td>
                        <td>
                            <?php echo isset($row['sysUser']) ? $row['sysUser'] : 'N/A'; ?>
                        </td>
                        <td>
                            <?php echo isset($row['job']) ? $row['job'] : 'N/A'; ?>
                        </td>
                        <td>
                            <?php echo isset($row['comment']) ? $row['comment'] : 'N/A'; ?>
                        </td>
                        <td>
                            <?php echo isset($row['date']) ? $row['date'].' '.(isset($row['time']) ? $row['time'] : '') : 'N/A'; ?>
                        </td>
                        <td>
                            <?php if (isset($row['id'])) { ?>
                            <a href="javascript:void(0);" class="job-delete btn btn-danger" data-route="delete.php?id=<?php echo $row['id']; ?><?php echo isset($_GET['view_history']) ? '&view_history' : ''; ?>">Delete</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if (!isset($_GET['view_history'])) { ?>
                <?php if ($jobTotal - $jobScheduled !== 0) { ?>
                    <a href="index.php?view_history" class="btn btn-primary">
                        View history (<?php echo $jobTotal - $jobScheduled; ?>)
                    </a>
                <?php } ?>
            <?php } else { ?>
                <a href="index.php" class="btn btn-warning">
                    Hide history
                </a>
            <?php } ?>

        </div>

        <script src="js/jquery.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script src="js/jquery.inputmask.min.js"></script>
        <script src="js/jquery.timepicker.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script>
            <?php list($minHours, $minMinutes) = explode(':', C_APP_DEFAULT_TRIGGER_TIME); ?>
            jQuery(function($) {
                var minDate = new Date();
                minDate.setHours(<?php echo $minHours; ?>);
                minDate.setMinutes(<?php echo $minMinutes; ?>);

                var tomorrowDate = new Date();

                <?php if (!C_APP_CUSTOM_TIME_ENABLED) { ?>
                tomorrowDate.setDate(tomorrowDate.getDate() + 1);
                <?php } ?>

                $('.datepicker').datepicker({
                    dateFormat: 'yy-mm-dd',
                    minDate: minDate >= new Date() ? minDate : tomorrowDate
                });

                $('.datepicker').inputmask("9999-99-99",{ "placeholder": "_" });

                $('.timepicker').timepicker({
                    timeFormat: 'HH:mm',
                    interval: 10
                });

                $('.timepicker').inputmask("99:99",{ "placeholder": "_" });

                $(document).on('click', '.job-delete', function() {
                    if (confirm('Are you sure to delete this planned push?')) {
                        <?php if (C_APP_PASSWORD_ENABLED) { ?>
                        var password = '';
                        if (password = prompt('Please, fill your password')) {
                            document.location.href = $(this).data('route')+'&password='+password;
                        }
                        <?php } else { ?>
                        if (confirm('Are you really sure?')) {
                            document.location.href = $(this).data('route');
                        }
                        <?php } ?>
                    }
                });
            });
        </script>
    </body>
</html>
