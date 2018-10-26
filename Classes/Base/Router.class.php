<?php

class Router
{
    /**
     * This holds the ConfigHelper object containing the parsed config file
     *
     * @var ConfigHelper $config
     */
    private $config;


    public function __construct() {
        $this->config = new ConfigHelper('config.yml');

        // get path relative to the project URL path
        $relativePath = str_replace(ROOT, '', $_SERVER['REQUEST_URI']);

        $routes = $this->config->get('routing:routes');

        if ($routes == null) {
            throw new Exception('Router: There are no routes defined in the config.yml!');
        }

        $routeFound = false;

        foreach ($routes as $route) {
            $routeRegex = $this->buildRegexForRoute($route['pattern']);

            if (preg_match("/$routeRegex/i", $relativePath, $matches)) {
                $routeFound = true;

                $callable = $route['handler'];

                // for non-static methods, create a class instance first
                if (is_array($callable)) {
                    $class = new $callable['class']();
                    $callable = array($class, $callable['method']);
                }

                $params = $this->buildParameters($matches);

                call_user_func($callable, $params);
                break;
            }
        }

        if (!$routeFound) {
            http_response_code(404);
        }
    }

    /**
     * Takes the route from the config and builds a regex for the route
     *
     * @param string $route
     * @return string
     */
    private function buildRegexForRoute($route) {
        $varConditions = $this->config->get('routing:conditions');

        // replace variables with corresponding regex, including parentheses for matching
        $route = preg_replace_callback('/\(\$(\w+)\)/', function($matches) use ($varConditions) {
            $var = $matches[1];
            $condition = isset($varConditions[$var]) ? $varConditions[$var] : '[^/]+';

            return "(?<$var>$condition)";
        }, $route);

        // escape slashes
        $route = str_replace('/', '\/', $route);

        // allow trailing slash, whole string must match (^$)
        $route = '^' . $route . '\/?$';

        return $route;
    }

    /**
     * Returns an associative array with the matched route variables/conditions and POST values
     *
     * @param array $matches
     * @return array
     */
    private function buildParameters($matches) {
        // remove full match (first element) from matches, not needed
        array_shift($matches);

        $params = array();

        foreach ($matches as $var => $match) {
            // ignore numbered indexes from matches
            if (!is_numeric($var)) {
                $params[$var] = $match;
            }
        }

        // add POST data to the params array, if POST request
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            foreach ($_POST as $var => $value) {
                $params[$var] = $value;
            }
        }

        return $params;
    }
}