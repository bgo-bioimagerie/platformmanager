<?php

/**
 * Download a file from a form input
 */
class Download
{
    /**
     *
     * @param string $file
     * @return string
     */
    public static function downloadFile($file)
    {
        if (file_exists($file)) {
            $fileNameArray = explode("/", $file);
            $fileName = $fileNameArray[count($fileNameArray)-1];

            header("Content-Type: application/json");
            header("Content-Disposition: attachment; filename=$fileName");
            header("Content-Length: " . filesize("$file"));
            $fp = fopen("$file", "r");
            fpassthru($fp);
        } else {
            echo "no file exists";
        }
    }
}
