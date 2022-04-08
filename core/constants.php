<?php

include_once 'config.php';

// Dir constants
const DIR_JOBS = 'jobs';
const DIR_DB = 'database';

// Database constants
const DB_TABLE_JOB = 'jobs.json';

// Auth user groups constants
const AUTH_GROUP_ALL = 'all';
const AUTH_USER_ANONYMOUS = 'anon';
const AUTH_ACL_SUFFIX = '_GROUPS';

// Auth right management constants
const APP_ACL_TIME_EDIT = 'APP_ACL_TIME_EDIT';
const APP_ACL_IGNORE_PASSWORD = 'APP_ACL_IGNORE_PASSWORD';

// Customizable constants
defined('APP_TITLE') or define('APP_TITLE', 'AT Manager');
defined('APP_DEFAULT_TRIGGER_TIME') or define('APP_DEFAULT_TRIGGER_TIME', '06:00');
defined('APP_CUSTOM_TIME_ENABLED') or define('APP_CUSTOM_TIME_ENABLED', false);
defined('APP_PASSWORD_VALUE') or define('APP_PASSWORD_VALUE', 'admin');
defined('APP_PASSWORD_ENABLED') or define('APP_PASSWORD_ENABLED', false);
defined('APP_ACL_TIME_EDIT'.AUTH_ACL_SUFFIX) or define('APP_ACL_TIME_EDIT'.AUTH_ACL_SUFFIX, [AUTH_GROUP_ALL]);
defined('APP_ACL_IGNORE_PASSWORD'.AUTH_ACL_SUFFIX) or define('APP_ACL_IGNORE_PASSWORD'.AUTH_ACL_SUFFIX, []);

?>