<?php

/**
 * This class makes basic stuff like the DB and the View available for classes extending it
 */
class Base
{
    /** @var View $view */
    public $view;

    /** @var PDO $DB */
    public $DB;

    private $dbHost = 'localhost';
    private $dbUser = 'root';
    private $dbPass = '';
    private $dbName = 'money_manager';


    public function __construct() {
        $this->view = new View();

        $this->DB = new PDO("mysql:host=$this->dbHost;dbname=$this->dbName;charset=utf8", $this->dbUser, $this->dbPass, array(
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ));
    }
}