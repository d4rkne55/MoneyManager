<?php

class Router
{
    /**
     * This associative array consists of paths, with named variables in the form ($variable),
     * and of the corresponding methods to be called when the path matches.
     * Paths are handled case-insensitive
     *
     * Static methods need to be in the form 'Class::staticMethod' and methods needing the class to be instantiated
     * need to be an array in the following form: array('Class', 'method')
     *
     * @var array $routes
     */
    private $routes = array(
        '' => array(
            MoneyManager::class, 'showTransfers'
        ),
        '($id)' => array(
            MoneyManager::class, 'showTransfers'
        ),
        '($id)/($dateRange)' => array(
            MoneyManager::class, 'showTransfers'
        ),
        'add' => array(
            MoneyManager::class, 'addTransfer'
        )
    );

    /**
     * This array defines the regex to be matched for the variables in the route
     * Slashes will get escaped, no need to do that manually
     *
     * @var array $varConditions
     */
    private $varConditions = array(
        'id'        => '\d+',
        'dateRange' => '.*'
    );


    public function __construct() {
        // get path relative to the project URL path
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $relativePath = str_replace(ROOT, '', $path);

        foreach ($this->routes as $route => $fnc) {
            $routeRegex = $this->buildRegexForRoute($route);

            if (preg_match("/$routeRegex/i", $relativePath, $matches)) {
                // remove full match (first element) from matches, not needed
                array_shift($matches);

                // give matches/parameters a named index (using variable name from route)
                $params = array();
                $paramNames = array_keys($this->varConditions);
                foreach ($paramNames as $i => $name) {
                    $params[$name] = isset($matches[$i]) ? $matches[$i] : '';
                }
                // add POST data to the params array, if POST request
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    foreach ($_POST as $var => $value) {
                        $params[$var] = $value;
                    }
                }

                // for non-static methods, create a class instance first
                if (is_array($fnc)) {
                    $class = new $fnc[0]();
                    $fnc = array($class, $fnc[1]);
                }

                call_user_func($fnc, $params);
                break;
            }
        }
    }

    private function buildRegexForRoute($route) {
        // replace variables with corresponding regex, including parentheses for matching
        $route = preg_replace_callback('/\(\$(\w+)\)/', function($matches) {
            return '(' . $this->varConditions[ $matches[1] ] . ')';
        }, $route);

        // escape slashes
        $route = str_replace('/', '\/', $route);

        // allow trailing slash, whole string must match (^$)
        $route = '^' . $route . '\/?$';

        return $route;
    }
}
