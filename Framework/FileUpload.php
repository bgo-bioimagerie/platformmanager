<?php

/**
 * Implement static function to manage file exange
 */
class FileUpload {

    /**
     * Upload a file to the server
     * @param type $target_dir
     * @param type $uploadFile_id
     * @return string
     */
    public static function uploadFile($target_dir, $uploadFile_id, $targetName) {
        $target_file = $target_dir . $targetName;

        $uploadOk = 1;
        //$imageFileType = pathinfo($_FILES[$uploadFile_id]["name"], PATHINFO_EXTENSION);
        // Check file size
        if ($_FILES[$uploadFile_id]["size"] > 500000000) {
            return "Error: your file is too large.";
            //$uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            return "Error: your file was not uploaded.";
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES[$uploadFile_id]["tmp_name"], $target_file)) {
                return "The file file" . basename($_FILES[$uploadFile_id]["name"]) . " has been uploaded.";
            } else {
                return "Error, there was an error uploading your file.";
            }
        }
    }

}
