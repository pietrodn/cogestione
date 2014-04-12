<?php
# Configuration template
# DON'T EDIT THIS FILE; edit config.php instead.

# Import DB_HOST, DB_NAME, DB_USER, DB_PASSWORD
require_once("../../external_includes/mysql_pw.inc");

# Form enabling
define('START_TIME', "20 january 2014, 14:00");
define('END_TIME', "23 january 2014, 18:00");

# Override the time settings?
define('COGE_MANUAL', FALSE);
define('COGE_MANUAL_ENABLED', FALSE);

# Edit cogestione data
#$coge_users[] = Array('user' => 'user', 'pass' => 'password');

?>