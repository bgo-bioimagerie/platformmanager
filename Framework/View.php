<?php

require_once 'Configuration.php';

/**
 * Class model the view.
 *
 * @author Sylvain Prigent
 */
class View {

    /** Name of the file associted to the view */
    private $file;

    /**
     * Constructor
     * 
     * @param string $action Action to which the view is associated
     * @param string $controller Controller to which the view is associated
     */
    public function __construct($action, $controller = "", $module = "") {
        $file = 'Modules/' . $module . '/View/' . $controller . "/" . $action . '.php';
        echo "view file = " . $file . "<br/>";
        if (file_exists($file)) {
            $this->file = $file;
        }
    }

    public function setFile($fileURL) {
        $this->file = $fileURL;
    }

    /**
     * Generate and send the view
     * 
     * @param array $data data that fill the view
     */
    public function generate($data) {

        $rootWeb = Configuration::get("rootWeb", "/");
        $data['rootWeb'] = $rootWeb;

        // Generate the dedicated part of the view
        extract($data);
        include ($this->file);

        return;
    }

    /**
     * Generate a view file and return the it's content
     * 
     * @param string $file URL of the view file vue to generate
     * @param array $data Needed data to generate the view
     * @return string Generated view
     * @throws Exception If the view file is not found
     */
    private function generatefile($file, $data) {
        if (file_exists($file)) {
            // sent the $data table elements accessibles in the view
            extract($data);

            ob_start();

            require $file;

            return ob_get_clean();
        } else {
            throw new Exception("unable to find the file in view: '$file' ");
        }
    }

    /**
     * Clean values inseted into HTML page for security
     * 
     * @param string $value Value to clean
     * @return string Value cleaned
     */
    private function clean($value) {
        // Convert special char to HTML
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }

}
