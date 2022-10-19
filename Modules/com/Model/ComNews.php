<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ComNews extends Model
{
    /**
     * Create the site table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "com_news";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("title", "varchar(250)", "");
        $this->setColumnsInfo("content", "TEXT", "");
        $this->setColumnsInfo("media", "TEXT", "");
        $this->setColumnsInfo("date", "date", "");
        $this->setColumnsInfo("expires", "date", "");
        $this->primaryKey = "id";
    }

    public function getForSpace($id_space, $limit = -1)
    {
        if ($limit <= 0) {
            $sql = "SELECT * FROM com_news WHERE id_space=?";
            $req = $this->runRequest($sql, array($id_space));
        } else {
            $sql = "SELECT * FROM com_news WHERE id_space=? LIMIT ?";
            $req = $this->runRequest($sql, array($id_space, $limit));
        }
        return $req->fetchAll();
    }

    public function getByDate($id_space, $limit = -1)
    {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM com_news WHERE id_space=? AND expires>=? ORDER BY date ASC LIMIT ".$limit.";";
        $req = $this->runRequest($sql, array($id_space, $today));
        return $req->fetchAll();
    }

    public function set($id, $id_space, $title, $content, $date, $expire)
    {
        if ($date == "") {
            $date = null;
        }
        if ($this->isNews($id_space, $id)) {
            $sql = "UPDATE com_news SET title=?, content=?, date=?, expires=?"
                    . " WHERE id=? AND id_space=?";
            $this->runRequest($sql, array($title, $content, $date, $expire, $id, $id_space));
            return $id;
        } else {
            $sql = "INSERT INTO com_news (id_space, title, content, date, expires) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_space, $title, $content, $date, $expire));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function isNews($id_space, $id)
    {
        $sql = "SELECT id FROM com_news WHERE id=? AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function setMedia($id_space, $id, $url)
    {
        $sql = "UPDATE com_news SET media=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($url, $id, $id_space));
    }

    public function getMedia($id_space, $id)
    {
        $sql = "SELECT media FROM com_news WHERE id=? AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $req[0];
    }

    public function get($id_space, $id)
    {
        if (!$id) {
            return array(
                "id" => 0,
                "id_space" => 0,
                "title" => "",
                "content" => "",
                "media" => "",
                "date" => date('Y-m-d'),
                "expires" => null
            );
        }
        $sql = "SELECT * FROM com_news WHERE id=? AND id_space=?";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function delete($id_space, $id)
    {
        // remove the file
        $sql = "SELECT * FROM com_news WHERE id=? AND id_space=?";
        $info = $this->runRequest($sql, array($id, $id_space))->fetch();
        if (file_exists($info["media"])) {
            unlink($info["media"]);
        }

        // remove the entry
        $sql2 = "DELETE FROM com_news WHERE id=? AND id_space=?";
        $this->runRequest($sql2, array($id, $id_space));
    }
}
