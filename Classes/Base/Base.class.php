<?php

/**
 * This class makes basic stuff like the DB and the View available for classes extending it
 */
class Base
{
    /**
     * This holds the ConfigHelper object containing the parsed config file
     *
     * @var ConfigHelper $config
     */
    public $config;

    /** @var View $view */
    public $view;

    /** @var PDO $DB */
    public $DB;


    public function __construct() {
        $this->config = new ConfigHelper('config.yml');

        $this->view = new View();

        // typecast array to an object for cleaner access inside string
        $conn = (object) $this->config->get('db_connection');

        if ($conn->database) {
            $this->DB = new PDO("mysql:host=$conn->host;dbname=$conn->database;charset=utf8", $conn->user, $conn->pass, array(
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ));
        }
    }
}