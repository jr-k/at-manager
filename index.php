<?php

    include 'config.php';

    $now = new \DateTime();

    $jobsIndex = [];
    $jobsRows = $jsonDb->select('*')->from(DB_TABLE_JOB)->order_by('date')->get();
    $alreadyInIndex = [];

    function jKey($jobItem) {
        return $jobItem['date'].';'.$jobItem['job'];
    }

    foreach($jobsRows as $row) {
        $jobsIndex[jKey($row)] = $row;
    }

    if (isset($_POST['jobs'])) {
        foreach($_POST['jobs'] as $job) {
            $m = microtime(true);
            $id = sprintf("%8x%05x",floor($m),($m-floor($m))*1000000);

            $newJob = [
                'atId' => '',
                'id' => $id,
                'job' => $job,
                'date' => $_POST['date'],
                'comment' => $_POST['comment'],
                'user' => $user
            ];

            $alreadyInIndex[$job] = array_key_exists(jKey($newJob), $jobsIndex);

            if ($alreadyInIndex[$job]) {
                continue;
            }

            // AT + récupérer l'id

            $jsonDb->insert(DB_TABLE_JOB, $newJob);
            $jobsRows[] = $newJob;
            $jobsIndex[jKey($newJob)] = $newJob;
        }

        if (!empty($alreadyInIndex)) {
            Header('Location:index.php');
        }
    }

?>

<html>
    <head>
        <meta charset="UTF-8" />
        <title>Checkpoint Push Scheduler</title>
        <link href="css/bootstrap.min.css" rel="stylesheet" />
        <link href="css/jquery-ui.min.css" rel="stylesheet" />
    </head>
    <body>
        <?php
            const JOB_DIR = 'jobs';
            $availableJobScripts = [];

            foreach (scandir(JOB_DIR) as $file) {
                if ($file == '.' || $file === '..') continue;
                $name = str_replace('.sh', '', $file);

                $availableJobScripts[] = [
                    'name' => $name,
                    'label' => ucfirst($name),
                    'file' => JOB_DIR.DIRECTORY_SEPARATOR.$file
                ];
            }
        ?>


        <div class="container">

            <h1><strong>Checkpoint Push Scheduler</strong></h1>
            <br />

            <div class="row">

                <?php if (!$alreadyInIndex) { ?>
                    <div class="col-lg-12">
                        <div class="alert alert-danger">
                            <strong>This push is already planned !</strong>
                        </div>
                    </div>
                <?php } ?>

                <form method="post" class="">
                    <?php foreach($availableJobScripts as $job) { ?>
                        <div class="col-lg-2">
                            <div class="form-group" style="margin-top:24px;">
                                <input id="job_<?php echo $job['name']; ?>" type="checkbox" name="jobs[]" value="<?php echo $job['file']; ?>" />
                                <label for="job_<?php echo $job['name']; ?>"><?php echo $job['label'] ?></label>
                            </div>
                        </div>
                    <?php } ?>


                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Reference</label>
                            <input type="text" name="comment" class="form-control" autocomplete="off" />
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="text" name="date" class="datepicker form-control" autocomplete="off" />
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success" style="margin-top:24px;margin-left:24px;">
                        Confirm
                    </button>
                </form>
            </div>



            <hr>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Job</th>
                        <th>Ref.</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobsRows as $row) { ?>
                    <?php
                        $jobDate = \DateTime::createFromFormat('Y-m-d H:i', $row['date'].' '.APP_TRIGGER_TIME);
                        $jobDateDiff = (int) $now->format('U') - (int) $jobDate->format('U');
                        $pastJob = $jobDateDiff >= 0;

                        if ($pastJob && !isset($_GET['view_history'])) continue;
                    ?>
                    <tr>
                        <td>
                            <div class="badge" style="font-size: 10px;">
                                <?php echo isset($row['id']) ? $row['id'] : 'N/A'; ?>
                            </div>
                        </td>
                        <td>
                            <?php echo isset($row['user']) ? $row['user'] : 'N/A'; ?>
                        </td>
                        <td>
                            <?php echo isset($row['job']) ? $row['job'] : 'N/A'; ?>
                        </td>
                        <td>
                            <?php echo isset($row['comment']) ? $row['comment'] : 'N/A'; ?>
                        </td>
                        <td>
                            <?php echo isset($row['date']) ? $row['date'].' '.APP_TRIGGER_TIME : 'N/A'; ?>
                        </td>
                        <td>
                            <?php if (isset($row['id'])) { ?>
                            <a href="javascript:void(0);" class="job-delete btn btn-danger" data-route="delete.php?id=<?php echo $row['id']; ?>">Delete</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if (!isset($_GET['view_history'])) { ?>
                <a href="index.php?view_history" class="btn btn-primary">
                    View history
                </a>
            <?php } else { ?>
                <a href="index.php" class="btn btn-warning">
                    Hide history
                </a>
            <?php } ?>

        </div>

        <script src="js/jquery.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script src="js/jquery.inputmask.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script>
            jQuery(function($) {
                $('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
                $('.datepicker').inputmask("9999-99-99",{ "placeholder": "_" });

                $(document).on('click', '.job-delete', function() {
                    if (confirm('Are you sure to delete this planned push?')) {
                        if (confirm('Are you really sure?')) {
                            document.location.href = $(this).data('route');
                        }
                    }
                });
            });
        </script>
    </body>
</html>
