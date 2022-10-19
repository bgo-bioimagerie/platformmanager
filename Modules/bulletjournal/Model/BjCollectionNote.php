<?php

require_once 'Framework/Model.php';

/**
 * @deprecated unused
 *
 * @author Sylvain Prigent
 */
class BjCollectionNote extends Model
{
    /**
     * Create the site table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "bj_j_collections_notes";
        $this->setColumnsInfo("id_collection", "int(11)", 0);
        $this->setColumnsInfo("id_note", "int(11)", 0);
    }

    public function getForCollection($id_space, $id)
    {
        $sql = "SELECT id_note FROM bj_j_collections_notes WHERE id_collection=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetchAll();
    }

    public function getNoteCollections($id_space, $id_note)
    {
        $sql = "SELECT bj_collections.name, bj_j_collections_notes.id_collection "
                 . "FROM bj_j_collections_notes "
                 . "INNER JOIN bj_collections ON bj_collections.id=bj_j_collections_notes.id_collection "
                 . "WHERE bj_j_collections_notes.id_note=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_note, $id_space))->fetchAll();
    }

    public function set($id_space, $id_collection, $id_note)
    {
        if (!$this->exists($id_space, $id_collection, $id_note)) {
            $sql = "INSERT INTO bj_j_collections_notes (id_collection, id_note, id_space) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_collection, $id_note, $id_space));
        }
    }

    public function exists($id_space, $id_collection, $id_note)
    {
        $sql = "SELECT * from bj_j_collections_notes WHERE id_collection=? AND id_note=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_collection, $id_note, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($id_space, $id_collection, $id_note)
    {
        $sql = "DELETE FROM bj_j_collections_notes WHERE id_collection=? AND id_note=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_collection, $id_note, $id_space));
    }
}
