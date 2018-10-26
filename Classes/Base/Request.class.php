<?php

class Request
{
    private $url = array();
    private $queryString = '';
    private $currentVar;

    private static $defaultPorts = array(
        'http' => 80,
        'https' => 443
    );


    public function __construct($url = null) {
        // use current URL if nothing passed
        if (!$url) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
            $url = "$protocol://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        $defaults = array(
            'scheme' => 'http',
            'path' => '/'
        );
        $url = array_merge($defaults, parse_url($url));

        $url['protocol'] = $url['scheme'];

        if (!isset($url['host']))  {
            $url['host'] = $url['path'];
            $url['path'] = $defaults['path'];
        }
        $url['domain'] = $url['host'];

        if (!isset($url['port']) && isset(self::$defaultPorts[ $url['protocol'] ])) {
            $url['port'] = self::$defaultPorts[ $url['protocol'] ];
        }

        if (isset($url['query'])) {
            $this->queryString = $url['query'];

            $query = array();
            foreach (explode('&', $url['query']) as $param) {
                if (strpos($param, '=') !== false) {
                    $param = explode('=', $param);
                    $query[ $param[0] ] = urldecode($param[1]);
                } else {
                    $query[$param] = '';
                }
            }

            $url['query'] = $query;
        } else {
            $url['query'] = array();
        }

        $this->url = $url;
    }

    public function get($part) {
        if (isset($this->url[$part])) {
            return $this->url[$part];
        } else {
            return false;
        }
    }

    /**
     * Returns the URL without query parameters or fragments
     *
     * @return string
     */
    public function getBaseUrl() {
        $url = $this->url['protocol'] . '://';
        $url .= $this->url['domain'];

        if (isset($this->url['port'])) {
            $port = $this->url['port'];

            if ($port != self::$defaultPorts[ $this->url['protocol'] ]) {
                $url .= ":$port";
            }
        }

        $url .= $this->url['path'];

        return $url;
    }

    /**
     * Returns the query string, unmodified
     *
     * @return string
     */
    public function getFullQuery() {
        return empty($this->queryString) ? '' : '?' . $this->queryString;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key) {
        $current = ($this->currentVar === null) ? $this->url : $this->currentVar;
        $this->currentVar = null;

        return (is_array($current) && array_key_exists($key, $current));
    }

    public function __get($var) {
        if ($this->currentVar === null) {
            $this->currentVar = $this->url;
        }

        $current = $this->currentVar;

        if (isset($current[$var])) {
            if (is_array($current[$var])) {
                $this->currentVar = $current[$var];

                return $this;
            } else {
                $this->currentVar = null;

                return $current[$var];
            }
        }

        $this->currentVar = null;

        return false;
    }
}