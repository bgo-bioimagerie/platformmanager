<?php

require_once 'Framework/Model.php';

/**
 * Class defining projects tasks
 *
 * @author Sylvain Prigent
 */
class SeTask extends Model {

        public function __construct() {
        $this->tableName = "se_task";
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `se_task` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_space` int(11) NOT NULL,
            `id_project` int(11) NOT NULL,
            `id_user` int(11),
            `state` int(11) NOT NULL,
            `name` varchar(120) NOT NULL DEFAULT '',
            `content` varchar(250) NOT NULL DEFAULT '',
            `start_date` DATE,
		    `end_date` DATE,
            `done` BIT DEFAULT 0,
            `private` BIT DEFAULT 0,
            PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);

        $sql2 = "CREATE TABLE IF NOT EXISTS `se_task_service` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_task` int(11) NOT NULL,
            `id_service` int(11) NOT NULL,
            `id_space` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        );";

        $this->runRequest($sql2);
    }

    public function getForSpace($id_space) {
        $sql = "SELECT * FROM se_task WHERE id_space=? AND deleted=0;";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }

    public function getById($id_space, $id_task) {
        $sql = "SELECT * FROM se_task WHERE id=? id_space=? AND deleted=0;";
        $req = $this->runRequest($sql, array($id_task, $id_space));
        return $req->fetch();
    }

    public function getByProject($id_project, $id_space) {
        $sql = "SELECT * FROM se_task WHERE id_space=? AND id_project=? AND deleted=0;";
        $req = $this->runRequest($sql, array($id_space, $id_project));
        return $req->fetchAll();
    }

    public function getByUser($id_user, $id_space) {
        $sql = "SELECT * FROM se_task WHERE id_space=? AND id_user=? AND deleted=0;";
        $req = $this->runRequest($sql, array($id_space, $id_user));
        return $req->fetchAll();
    }

    public function getByProjectAndUser($id_project, $id_user, $id_space) {
        $sql = "SELECT * FROM se_task WHERE id_space=? AND id_project=? id_user=? AND deleted=0;";
        $req = $this->runRequest($sql, array($id_space, $id_project, $id_user));
        return $req->fetchAll();
    }

    public function getByPeriodForProject($id_project, $id_space, $beginPeriod, $endPeriod) {
        $sql = "SELECT * FROM se_task WHERE id_project=? AND start_date>=? AND end_date<? AND $id_space=? AND deleted=0;";
        $req = $this->runRequest($sql, array($id_project, $beginPeriod, $endPeriod, $id_space));
        return $req->fetchAll();
    }

    public function setPrivate($id_space, $id_task, $private) {
        $sql = "UPDATE se_task SET private=? WHERE id=? AND id_sapce=?;";
        $this->runRequest($sql, array($private, $id_task, $id_space));
    }

    public function isPrivate($id_space, $id_task) {
        $sql = "SELECT private FROM se_task WHERE id=? AND id_space=?;";
        $req = $this->runRequest($sql, array($id_task, $id_space));
        $tmp = $req->fetch();
        return $tmp['private'] == 1;
    }
    
    public function set($id, $id_space, $id_project, $state, $name, $content, $start_date, $end_date, $services, $id_user, $done, $private) {
        if ($this->isTask($id_space, $id)) {
            $sql = "UPDATE se_task SET id_project=?, state=?, name=?, content=?, start_date=?, end_date=?, id_user=?, done=?, private=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($id_project, $state, $name, $content, $start_date, $end_date, $id_user, $done, $private, $id, $id_space));
        } else {
            $sql = "INSERT INTO se_task (id_project, state, name, content, start_date, end_date, id_user, done, private, id_space) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $this->runRequest($sql, array($id_project, $state, $name, $content, $start_date, $end_date, $id_user, $done, $private, $id_space));
            $id = $this->getDatabase()->lastInsertId();
        }
        if (!empty($services)) {
            foreach($services as $service) {
                $this->setTaskService($id_space, $id, $service);
            }
        }

        return $id;
    }
    
    public function isTask($id_space, $id) {
        $sql = "SELECT * FROM se_task WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function delete($id_space, $id_task) {
        $sql = "UPDATE se_task SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id_task, $id_space));
    }

    ///// TASK_SERVICE METHODS /////

    public function getTaskServices($id_space, $id_task) {
        $sql = "SELECT * FROM se_task_service WHERE id_space=? AND id_task=? AND deleted=0;";
        $req = $this->runRequest($sql, array($id_space, $id_task));
        return $req->fetchAll();
    }

    public function getTaskServicesIds($id_space, $id_task) {
        $result = [];
        $sql = "SELECT id_service FROM se_task_service WHERE id_space=? AND id_task=? AND deleted=0;";
        $req = $this->runRequest($sql, array($id_space, $id_task));
        $response = $req->fetchAll();
        foreach($response as $elem) {
            array_push($result, $elem['id_service']);
        }
        return $result;
    }

    public function setTaskService($id_space, $id_task, $id_service) {
        if ($this->isTaskService($id_space, $id_task, $id_service)) {
            $sql = "UPDATE se_task_service (id_space, id_task, id_service) VALUES (?, ?, ?)";
                $this->runRequest($sql, array($id_space, $id_task, $id_service));
        } else {
            $sql = "INSERT INTO se_task_service (id_space, id_task, id_service) VALUES (?, ?, ?)";
            $this->runRequest($sql, array($id_space, $id_task, $id_service));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function isTaskService($id_space, $id_task, $id_service) {
        $sql = "SELECT * FROM se_task_service WHERE id_task=? AND id_service=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_task, $id_service, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function deleteTaskService($id_space, $id_task, $id_service) {
        $sql = "UPDATE se_task_service SET deleted=1,deleted_at=NOW() WHERE id_task=? AND id_service=? AND id_space=?";
        $this->runRequest($sql, array($id_task, $id_service, $id_space));
    }

    public function deleteAllTaskServices($id_space, $id_task) {
        $task_services = $this->getTaskServices($id_space, $id_task);
        if (!empty($task_services)) {
            foreach($task_services as $task_service) {
                $this->deleteTaskService($id_space, $id_task, $task_service['id']);
            }
        }
    }

}