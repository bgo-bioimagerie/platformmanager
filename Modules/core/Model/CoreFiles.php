<?php
require_once 'Framework/Errors.php';
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';

/**
 * Generic file handler
 */
class CoreFiles extends Model {

    public static int $READY=0;
    public static int $PENDING=1;
    public static int $IN_PROGRESS=2;
    public static int $ERROR=3;


    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `core_files` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(150) NOT NULL,
        `id_space` int(11) NOT NULL,
        `id_user` int(11),
        `module` varchar(30) NOT NULL,
        `role` int(11) NOT NULL,
        `status` int NOT NULL DEFAULT 0,
        `msg` varchar(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    }

    /**
     * Download file
     * 
     * @param CoreFile $file file entry
     */
    public function download($file) {
        $path = $this->path($file);
        if($path == null || !file_exists($path)) {
            Configuration::getLogger()->warning('file not found', ['file' => $path]);
            throw new PfmFileException('file does not exists', 404);
        }
        $mime = mime_content_type($path);
        header('Content-Description: File Transfer');
        header('Content-Type: '.$mime);
        header('Content-Disposition: attachment; filename="'.$file['name'].'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }

    /**
     * Upload entry file to disk, file taken from request
     * 
     * @param CoreFile $file file entry
     * @param string $formFileId  name of request parameter for this file
     */
    public function upload($file, $formFileId) {
        $path = $this->path($file);
        if($path == null) {
            Configuration::getLogger()->warning('file not found', ['file' => $path]);
            throw new PfmFileException('file does not exists', 404);
        }
        $base = dirname($path);
        $name = basename($path);
        
        if(!is_dir($base)) {
            mkdir($base, 0777, true);
        }
        FileUpload::uploadFile($base, $formFileId, $name);
    }

    /**
     * Return name used to save file
     */

    private function internalName($file) {
        $path_parts = pathinfo($file['name']);
        $extension = $path_parts['extension'];
        $id = $file['id'];
        return "$id.$extension";
    }

    /**
     * Get path to file
     */
    public function path($file) {
        if(!$file) {
            return null;
        }
        $rootDir = Configuration::get('data_path', '.');
        return sprintf("%s/data/files/%s/%s/%s", $rootDir, $file['id_space'], $file['module'], $this->internalName($file));
    }

    /*
    * Copy file located at $path (uploaded, saved, ..) to expected location
    */
    public function copyFile($file, $path) {
        $dest = $this->path($file);
        $destDirName = dirname($dest);
        if(!is_dir($destDirName)) {
            mkdir($destDirName, 0777, true);
        }
        copy($path, $dest);
    }

    /*
    * Copy data to expected location
    */
    public function copyData($file, $data) {
        $dest = $this->path($file);
        $destDirName = dirname($dest);
        if(!is_dir($destDirName)) {
            mkdir($destDirName, 0777, true);
        }
        file_put_contents($dest, $data);
    }

    /**
     * Get file entry
     */
    public function get($id) {
        $sql = "SELECT * FROM core_files WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    /**
     * Delete file entry and delete related file
     */
    public function delete($id) {
        $file = $this->get($id);
        if(!$file) {
            return;
        }
        unlink($this->path($file));
        $sql = "DELETE FROM core_files WHERE id=?";
        $this->runRequest($sql, array($id));
    }

    /**
     * Create/update a new file entry
     */
    public function set($id, $id_space, $name, $role, $module, $id_user) {
        if (!$id) {
            $sql = 'INSERT INTO core_files (id_space, `name`, module, `role`, id_user) VALUES (?,?,?,?,?)';
            $this->runRequest($sql, array($id_space, $name, $module, $role, $id_user));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE core_files SET id_space=?, `name`=?, module=?, `role`=?, id_user=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $name, $module, $role, $id_user, $id));
            return $id;
        }
    }

    public function status(int $id_space, int $id, int $status, string $msg) {
        $sql = 'UPDATE core_files SET status=?,msg=? WHERE id=? AND id_space=? AND deleted=0';
        $this->runRequest($sql, array($status, $msg, $id, $id_space));  
    }

    public function getByModule(int $id_space, string $module, int $role) {
        $sql = "SELECT core_files.*, core_users.login as login FROM core_files INNER JOIN core_users ON core_users.id=core_files.id_user WHERE core_files.id_space=? AND core_files.module=? and core_files.role>=? ORDER BY core_files.id DESC";
        return $this->runRequest($sql, array($id_space, $module, $role))->fetchAll();
    }

}


?>