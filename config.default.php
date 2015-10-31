<?php

# Configuration template for automatic setup

# Set $db_host, $db_name, $db_user, $db_password for database connection
$db_host = 'DB_HOST'; # MySQL host
$db_name = 'DB_NAME'; # MySQL database name
$db_user = 'DB_USER'; # MySQL user with access to $db_name
$db_password = 'DB_PASSWORD'; # Password of MySQL user

# Credentials for editing cogestione data. Uncomment and copy.
$cgUsers[] = Array(
              Array('user' => 'ADMIN_USER0', 'pass' => 'ADMIN_PASSWORD0'),
              # Add other admin users, or delete the following array
              Array('user' => 'ADMIN_USER1', 'pass' => 'ADMIN_PASSWORD1'),
             );

?>
