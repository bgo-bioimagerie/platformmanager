<?php

require_once 'Configuration.php';
require_once 'Framework/Errors.php';

/**
 * Class model the view.
 *
 * @author Sylvain Prigent
 */
class View
{
    /** Name of the file associted to the view */
    private string $file;

    /**
     * Constructor
     *
     * @param string $action Action to which the view is associated
     * @param string $controller Controller to which the view is associated
     */
    public function __construct($action, $controller = "", $module = "")
    {
        $file = 'Modules/' . strtolower($module) . '/View/' . $controller . "/" . $action . '.php';
        if (file_exists($file)) {
            $this->file = $file;
        }
    }

    /**
     *
     * @param string $fileURL
     */
    public function setFile($fileURL)
    {
        $this->file = $fileURL;
    }

    /**
     * Generate and send the view
     *
     * @param array $data data that fill the view
     */
    public function generate($data)
    {
        $rootWeb = Configuration::get("rootWeb", "/");
        $data['rootWeb'] = $rootWeb;
        // Generate the dedicated part of the view
        extract($data);
        if (!file_exists($this->file) || !$this->file) {
            throw new PfmException('Template not found or undefined', 500);
        }
        include($this->file);
    }

    /**
     * Clean values inseted into HTML page for security
     *
     * @param string $value Value to clean
     * @return string Value cleaned
     */
    private function clean($value)
    {
        // Convert special char to HTML
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
}
