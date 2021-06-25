<?php

require_once 'Framework/Configuration.php';
require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreUser.php';

class Helpdesk extends Model {

    public static $TYPE_EMAIL = 0;
    public static $TYPE_NOTE = 1;

    public static $STATUS_NEW = 0;
    public static $STATUS_OPEN = 1;
    public static $STATUS_REMINDER = 2;
    public static $STATUS_CLOSED = 3;

    /**
     * Create the stats_buckets table
     * 
     * @return PDOStatement
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `hp_tickets` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_space` int(11) NOT NULL,
            `subject` varchar(200) NOT NULL,
            `status` int(11) NOT NULL,
            `queue` varchar(30),
            `created_by` varchar(30),  /* email of user */
            `created_by_user` int(11), /* if user is a customer get its id */
            `created_at` DATETIME,
            `assigned` int(11),  /* user id ticket is assigned to */
            `assigned_name` varchar(30),  /* name of assignee */
            `reminder` DATE,
            `reminder_sent`int(1) DEFAULT 0,
            PRIMARY KEY (`id`)
            );";
        $this->runRequest($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `hp_ticket_message` (
            `created_at` DATETIME,
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_ticket` int(11) NOT NULL,
            `subject` varchar(200) NOT NULL,
            `body` TEXT,
            `from` varchar(200),
            `to` varchar(250),
            `type` int(11) DEFAULT 0,
            PRIMARY KEY (`id`)
            );";
        $this->runRequest($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `hp_ticket_attachment` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_ticket` int(11) NOT NULL,
            `id_message` int(11) NOT NULL,
            `id_file` int(11),
            `name_file` varchar(200),
            PRIMARY KEY (`id`)
            );";
        $this->runRequest($sql);
    }

    /**
     * Creates a new ticket
     * 
     * @param string $id_space space identifier
     * @param string $from mail or name of creator
     * @param string $to mail of destination
     * @param string $subject subject of ticket
     * @param string $body  content of ticket
     * @param array $files  list of CoreFiles
     * @return int id of ticket
     */
    public function createTicket($id_space, $from, $to, $subject, $body, $files=[], $id_user=0) {
        // create ticket
        // create message
        // create attachements
        // TODO notify followers
        $id_ticket = $this->ticketFromSubject($subject);
        if($id_ticket == 0) {
            $sql = 'INSERT INTO `hp_tickets` (`id_space`, `status`, `subject`,  `created_by`, `created_by_user`, `created_at`)  VALUES (?,?,?, ?,?, NOW())';
            $this->runRequest($sql, array($id_space, self::$STATUS_NEW, $subject, $from, $id_user));
            $id_ticket = $this->getDatabase()->lastInsertId();
        }

        $sql = 'INSERT INTO `hp_ticket_message` (`id_ticket`, `from`, `to`, `subject`, `body`, `type`, `created_at`)  VALUES (?,?,?,?,?,?, NOW())';
        $this->runRequest($sql, array($id_ticket, $from, $to, $subject, $body, self::$TYPE_EMAIL));
        $id_message = $this->getDatabase()->lastInsertId();

        foreach($files as $attachment) {
            $sql = 'INSERT INTO `hp_ticket_attachment` (id_ticket, id_message, id_file, name_file)  VALUES (?,?,?, ?)';
            $this->runRequest($sql, array($id_ticket, $id_message, $attachment['id'], $attachment['name']));
        }

        return $id_ticket;
    }

    public function addEmail($id_ticket, $body, $from, $files=[]) {
        // create message
        // TODO notify followers
        $sql = 'INSERT INTO hp_ticket_message (`id_ticket`, `from`, `body`, `type`, created_at)  VALUES (?,?,?,?, NOW())';
        $this->runRequest($sql, array($id_ticket, $from, $body, self::$TYPE_EMAIL));
        $id = $this->getDatabase()->lastInsertId();

        foreach($files as $attachment) {
            $sql = 'INSERT INTO hp_ticket_attachment (id_ticket, id_file, name_file)  VALUES (?,?, ?)';
            $this->runRequest($sql, array($id_ticket, $attachment['id'], $attachment['name']));
        }
        return $id;
    }

    public function addNote($id_ticket, $body, $from) {
        // create message
        // TODO notify followers
        $sql = 'INSERT INTO hp_ticket_message (`id_ticket`, `from`, `body`, `type`, created_at)  VALUES (?,?,?,?, NOW())';
        $this->runRequest($sql, array($id_ticket, $from, $body, self::$TYPE_NOTE));
        return $this->getDatabase()->lastInsertId();

    }

    /**
     * Check subject to see if related to an existing ticket
     * 
     * @param string $subject
     * @return int id of ticket, else 0
     */
    public function ticketFromSubject($subject) {
        preg_match('/[Ticket #(\d+)]/', $subject, $matches, PREG_OFFSET_CAPTURE);
        if(!$matches) {
            return 0;
        }
        foreach($matches as $match) {
            $res = $this->get($match[0]);
            if($res) {
                return $res;
            }
        }
        return 0;
    }

    public function replyToTicket($id_ticket, $id_message, $body, $from, $files=[]) {
        // TODO
        // create message
        // create attachements
        // send message
    }

    public function assign($id_ticket, $id_user) {
        $um = new CoreUser();
        $login = $um->getUserLogin($id_user);
        $sql = "UPDATE hp_tickets set assigned=?, assigned_name=? WHERE id=?";
        $this->runRequest($sql, array($id_user, $login, $id_ticket));

    }

    public function setStatus($id_ticket, $status, $reminder_date=null) {
        if($status === self::$STATUS_REMINDER) {
            $sql = "UPDATE hp_tickets set `status`=?, reminder=?, reminder_set=0 WHERE id=?";
            $this->runRequest($sql, array($status, $reminder_date, $id_ticket));
        }
        $sql = "UPDATE hp_tickets SET `status`=? WHERE id=?";
        $this->runRequest($sql, array($status, $id_ticket));
    }

    public function list($id_space, $status=0, $id_user=0) {
        $sql = "SELECT * FROM hp_tickets WHERE `status`=?";
        if($id_user) {
            $sql .= " AND (assigned=? OR created_by_user=?)";
            return $this->runRequest($sql, array($status, $id_user))->fetchAll();
        }
        return $this->runRequest($sql, array($status))->fetchAll();
    }

    public function get($id_ticket) {
        $sql = "SELECT * FROM hp_tickets WHERE id=?";
        return $this->runRequest($sql, array($id_ticket))->fetch();
    }

    public function getMessages($id_ticket) {
        $sql = "SELECT * FROM hp_ticket_message WHERE id_ticket=?";
        return $this->runRequest($sql, array($id_ticket))->fetchAll();
    }

    public function getMessage($id) {
        $sql = "SELECT * FROM hp_ticket_message WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getAttachement($id) {
        $sql = "SELECT * FROM hp_ticket_attachment WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getAttachments($id_ticket, $id_message=0) {
        if($id_message) {
            $sql = "SELECT * FROM hp_ticket_attachment WHERE id_ticket=? AND id_message=?";
            return $this->runRequest($sql, array($id_ticket, $id_message))->fetchAll();
        }
        $sql = "SELECT * FROM hp_ticket_attachment WHERE id_ticket=?";
        return $this->runRequest($sql, array($id_ticket))->fetchAll();
    }

    public function move($id_ticket, $queue) {
        $sql = "UPDATE hp_tickets SET `queue`=? WHERE id=?";
        $this->runRequest($sql, array($queue, $id_ticket));
    }

    public function trash($id_ticket) {
        // TODO delete files too....
        $sql = "DELETE FROM hp_ticket_attachment WHERE id_ticket=?";
        $this->runRequest($sql, array($id_ticket));
        $sql = "DELETE FROM hp_ticket_message WHERE id_ticket=?";
        $this->runRequest($sql, array($id_ticket));
        $sql = "DELETE FROM hp_tickets WHERE id=?";
        $this->runRequest($sql, array($id_ticket));
    }
}

?>