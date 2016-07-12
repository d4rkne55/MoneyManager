<?php
require_once('dbconnect.php');

function money_to_number($string) {
    $num = str_replace(',', '.', $string);
    $num = preg_replace("/([^\d\.-]|\.(?=\d{3}))/", "", $num);
    return (float) $num;
}
function sql_escape($var) {
    global $DB;
    return $DB->real_escape_string($var);
}

if (!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
    $date = explode('.', $_POST["date"]);
    if (empty($date[2])) $date[2] = date('Y');
    echo sql_escape( strtotime(implode('.', $date)) );
    $usage = sql_escape( trim($_POST["usage"]) );
    $amount = sql_escape( money_to_number($_POST["amount"]) );
    $aid = (int) sql_escape($_POST["aid"]);

    $DB->query("INSERT INTO `money-manager_transfers` VALUES (default, $aid, $amount, '$usage', FROM_UNIXTIME($date))");
    //$DB->query("UPDATE `money-manager_accounts` SET Balance = Balance + $amount WHERE AccountID=$aid");
    //is calculated dynamically from the DB now..
}