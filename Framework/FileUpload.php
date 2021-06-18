<?php
require_once 'Framework/Errors.php';
/**
 * Implement static function to manage file exange
 * 
 *
 */

define("FILE_MAX_SIZE", 500_000_000);

class FileUpload {

    /**
     * Upload a file to the server
     * @param type $target_dir
     * @param type $uploadFile_id
     * @return string
     */
    public static function uploadFile($target_dir, $uploadFile_id, $targetName) {
        $target_file = $target_dir . $targetName;
        if ($_FILES[$uploadFile_id]["size"] > FILE_MAX_SIZE) {
            throw new PfmFileException("File size too large: ".FILE_MAX_SIZE, 1);
        }

        if(!move_uploaded_file($_FILES[$uploadFile_id]["tmp_name"], $target_file)) {
            throw new PfmFileException("Error, there was an error uploading your file");
        }
        return "The file file" . basename($_FILES[$uploadFile_id]["name"]) . " has been uploaded.";
    }

}
