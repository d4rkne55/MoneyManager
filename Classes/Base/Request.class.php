<?php

class Request
{
    private $url = array();
    private $query = '';
    private $currentVar;


    public function __construct($url) {
        $defaults = array(
            'protocol' => '',
            'port' => 80,
            'path' => '/'
        );
        $url = parse_url($url);

        if (isset($url['scheme'])) {
            $url['protocol'] = $url['scheme'];
            unset($url['scheme']);
        }

        if (isset($url['query'])) {
            $this->query = $url['query'];

            $query = array();
            foreach (explode('&', $url['query']) as $param) {
                if (strpos($param, '=') !== false) {
                    $param = explode('=', $param);
                    $query[ $param[0] ] = $param[1];
                } else {
                    $query[$param] = '';
                }
            }

            $url['query'] = $query;
        }

        $url = array_merge($defaults, $url);
        $this->url = $url;
    }

    public function get($part) {
        if (isset($this->url[$part])) {
            return $this->url[$part];
        } else {
            return false;
        }
    }

    public function getFullQuery() {
        return empty($this->query) ? '' : '?' . $this->query;
    }

    public function has($key) {
        return (is_array($this->currentVar) && array_key_exists($key, $this->currentVar));
    }

    public function dump() {
        return $this->url;
    }

    public function __get($var) {
        $current = $this->currentVar;
        if (isset($current[$var]) && !is_array($current[$var])) {
            $var = $current[$var];
            $this->currentVar = null;

            return $var;
        } else {
            $this->currentVar = $this->url[$var];

            return $this;
        }
    }
}