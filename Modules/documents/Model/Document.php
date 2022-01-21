<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class Document extends Model {

    public static $VISIBILITY_PRIVATE = 0; // space managers or admin
    public static $VISIBILITY_MEMBERS = 1; // all space members
    public static $VISIBILITY_USER = 2; // a specific user 
    public static $VISIBILITY_CLIENT = 3; // a specific client
    public static $VISIBILITY_PUBLIC = 10; // anyone

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "dc_documents";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("title", "varchar(250)", "");
        $this->setColumnsInfo("id_user", "int(11)", 0);
        $this->setColumnsInfo("date_modified", "date", "");
        $this->setColumnsInfo("url", "TEXT", "");
        $this->setColumnsInfo('visibility', 'int', 0);
        $this->setColumnsInfo('id_ref', 'int', ''); // according to visibility, user or client id
        $this->primaryKey = "id";
    }
    
    public function mergeUsers($users){
        for($i = 1 ; $i < count($users) ; $i++){
            $sql = "UPDATE dc_documents SET id_user=? WHERE id_user=?";
            $this->runRequest($sql, array($users[0], $users[$i]));
        }
    }
    
    public function getForSpace($id_space){
        $sql = "SELECT * FROM dc_documents WHERE id_space=?";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }
    
    public function add($id_space, $title, $id_user){
        $sql = "INSERT INTO dc_documents (id_space, title, id_user) VALUES (?,?,?)";
        $this->runRequest($sql,array($id_space, $title, $id_user));
        return $this->getDatabase()->lastInsertId();
    }
    
    public function edit($id, $id_space, $title, $id_user){
        $sql = "UPDATE dc_documents SET title=?, id_user=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($title, $id_user, $id, $id_space));
    }

    public function setVisibility($id_space, $id, $visibility, $id_ref) {
        $sql = "UPDATE dc_documents SET visibility=?, id_ref=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($visibility, $id_ref, $id, $id_space));   
    }

    public function getPublicDocs($id_space){
        $sql = "SELECT * FROM dc_documents WHERE id_space=? AND visibility=?";
        $req = $this->runRequest($sql, array($id_space, self::$VISIBILITY_PUBLIC));
        return $req->fetchAll();
    }

    public function getRestrictedDocs($id_space, $visibility, $id_ref=0) {
        if($visibility == self::$VISIBILITY_MEMBERS) {
            $sql = "SELECT * FROM dc_documents WHERE id_space=? AND visibility=?";
            $req = $this->runRequest($sql, array($id_space, self::$VISIBILITY_MEMBERS));
            return $req->fetchAll();            
        }
        $sql = "SELECT * FROM dc_documents WHERE id_space=? AND ((visibility=? AND id_ref=?) OR visibility=?)";
        $req = $this->runRequest($sql, array($id_space, $visibility, $id_ref, self::$VISIBILITY_PUBLIC));
        return $req->fetchAll();    
    }
    
    public function set($id, $id_space, $title, $id_user){
        if ($this->isDocument($id_space, $id)){
            $this->edit($id, $id_space, $title, $id_user);
            return $id;
        }
        else{
            return $this->add($id_space, $title, $id_user);
        }
    }
    
    public function isDocument($id_space, $id){
        $sql = "SELECT id FROM dc_documents WHERE id=? AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1){
            return true;
        }
        return false;
    }
    
    public function setUrl($id_space, $id, $url){
        $sql = "UPDATE dc_documents SET url=?, date_modified=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($url, date("Y-m-d", time()), $id, $id_space));
    }
    
    public function getUrl($id_space, $id){
        $sql = "SELECT url FROM dc_documents WHERE id=? AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $req[0];
    }
    
    public function get($id_space, $id){
        if (!$id){
            return array(
                "id" =>  0,
                "id_space" => 0,
                "title" => "",
                "id_user" =>  0,
                "date_modified" => null,
                "url" => "",
                "visibility" => 0,
                "id_ref" => 0
            );
        }
        $sql = "SELECT * FROM dc_documents WHERE id=? AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $req;
    }
    
    public function delete($id_space, $id){
        // remove the file
        $sql = "SELECT * FROM dc_documents WHERE id=? AND id_space=?";
        $info = $this->runRequest($sql, array($id, $id_space))->fetch();
        if (file_exists($info["url"])){
            unlink($info["url"]);
        }
        
        // remove the entry
        $sql2 = "DELETE FROM dc_documents WHERE id=? AND id_space=?";
        $this->runRequest($sql2, array($id, $id_space));
        
    }
}