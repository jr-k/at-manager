<?php

include_once 'constants.php';

$AUTH_USER = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : AUTH_USER_ANONYMOUS;
$AUTH_USERGROUPS = [$AUTH_USER, AUTH_GROUP_ALL];

define('AUTH_USER', $AUTH_USER);
define('AUTH_USERGROUPS', $AUTH_USERGROUPS);

function userCan($C_FEATURE) {
    foreach(AUTH_USERGROUPS as $AUTH_USERGROUP) {
        if (in_array($AUTH_USERGROUP, constant($C_FEATURE.AUTH_ACL_SUFFIX))) {
            return true;
        }
    }

    return false;
}
?>