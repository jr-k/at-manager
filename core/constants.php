<?php

// Generic app constants
const DIR_JOBS = 'jobs';
const DIR_DB = 'database';
const DB_TABLE_JOB = 'jobs.json';

// Custom app constants
defined('APP_TITLE') or define('C_APP_TITLE', 'AT Manager');
defined('APP_DEFAULT_TRIGGER_TIME') or define('C_APP_DEFAULT_TRIGGER_TIME', '06:00');
defined('APP_CUSTOM_TIME_ENABLED') or define('C_APP_CUSTOM_TIME_ENABLED', false);
defined('APP_PASSWORD_VALUE') or define('C_APP_PASSWORD_VALUE', 'admin');
defined('APP_PASSWORD_ENABLED') or define('C_APP_PASSWORD_ENABLED', false);

?>