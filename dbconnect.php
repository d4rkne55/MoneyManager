<?php
$DB = new MySQLi("localhost", "root", "", "privateprojects");

if ($DB->connect_error) {
    die("<em>Couldn't connect to the Database:</em> " . $DB->connect_error);
}
$DB->set_charset("utf8");