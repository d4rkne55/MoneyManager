<?php

class ConfigHelper
{
    /**
     * Holds the parsed YAML config as an (associative) array
     *
     * @var array $config
     */
    private $config = array();

    /**
     * Only for the ability of chaining the magic __get() method
     *
     * @var array $currVar
     */
    private $currVar = array();


    public function __construct($file) {
        $config = Spyc::YAMLLoad($file);
        $this->config = self::processValues($config);
    }

    /**
     * Further processing of the config values
     *
     * @param array $config
     * @return array
     */
    private static function processValues($config) {
        // splits non-static method strings into a callable array
        if (isset($config['routing']['routes'])) {
            array_walk($config['routing']['routes'], function(&$route) {
                $handler = &$route['handler'];

                if (strpos($handler, '->') !== false) {
                    $handler = explode('->', $handler);
                    $handler = array(
                        'class' => $handler[0],
                        'method' => $handler[1]
                    );
                }

                return $route;
            });
        }

        return $config;
    }

    /**
     * Loops through the config until the given path is reached and returns the value.
     * Returns null if the variable/path doesn't exist.
     *
     * The path can be a colon delimited string: 'foo:bar:baz'
     * or in PHP "key notation": 'foo[bar][baz]'
     * or even mixed: 'foo:barArray[0]:baz'
     *
     * Here's an example YAML matching the first two path examples:
     * foo:
     *     bar:
     *         baz: valueYouWantToGet
     *         idk: 1
     *     ...
     *
     * ...
     *
     * @param string $path
     * @return mixed
     */
    public function get($path) {
        $path = str_replace(array('[', ']'), array(':', ''), $path);
        $path = explode(':', $path);

        $var = $this->config;
        foreach ($path as $key) {
            if (array_key_exists($key, $var)) {
                $var = $var[$key];
            } else {
                $var = null;
                break;
            }
        }

        return $var;
    }

    /**
     * Pretty much the same as the get() method, just as object oriented notation:
     *
     * This equals the above two path examples: $configObject->foo->bar->baz
     *
     * @param $var
     * @return $this|mixed  returns only scalar values and null, no arrays because of chaining ability
     */
    public function __get($var) {
        $configVar = array_key_exists($var, $this->config) ? $this->config[$var] : null;
        $var = array_key_exists($var, $this->currVar) ? $this->currVar[$var] : $configVar;

        if (!is_array($var)) {
            $this->currVar = array();
            return $var;
        } else {
            $this->currVar = $var;
            return $this;
        }
    }
}