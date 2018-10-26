<?php

/**
 * Basic class for templating
 */
class View
{
    protected $templateDir;
    public $baseTemplate;
    private $subOutput;
    private $vars = array();


    public function __construct($templateDir = 'templates/') {
        if (substr($templateDir, -1) != '/') {
            $templateDir .= '/';
        }
        $this->templateDir = $templateDir;

        if (!file_exists($this->templateDir)) {
            throw new Exception("View: Template directory doesn't exist.");
        }
    }

    /**
     * Renders template with passed variables
     *
     * @param string $template  filename of the template to render
     * @param array  $vars      variables to pass to the template, optional
     */
    public function render($template, $vars = array()) {
        if (count($vars) > 0) {
            $this->vars = $vars;
        }

        $this->subOutput = $this->safeInclude($template);

        if ($this->baseTemplate) {
            echo $this->safeInclude($this->baseTemplate);

            $this->baseTemplate = null;
        } else {
            echo $this->subOutput;
        }
    }

    /**
     * @return string
     */
    public function renderSub() {
        return $this->subOutput;
    }

    /**
     * @param string $template
     * @return string
     * @throws Exception
     */
    private function safeInclude($template) {
        if (file_exists($this->templateDir . $template)) {
            ob_start();
            include($this->templateDir . $template);

            return ob_get_clean();
        } else {
            throw new Exception("View: Template '$template' not found!");
        }
    }

    public function __get($var) {
        if (isset($this->vars[$var])) {
            return $this->vars[$var];
        }

        return null;
    }
}