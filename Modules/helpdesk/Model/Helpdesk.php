<?php

require_once 'Framework/Configuration.php';
require_once 'Framework/Model.php';
require_once 'Framework/Email.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreFiles.php';
require_once 'Modules/core/Model/CoreUserSpaceSettings.php';

require_once 'Modules/helpdesk/Model/HelpdeskTranslator.php';

class Helpdesk extends Model
{
    public static $TYPE_EMAIL = 0;
    public static $TYPE_NOTE = 1;

    public static $STATUS_NEW = 0;
    public static $STATUS_OPEN = 1;
    public static $STATUS_REMINDER = 2;
    public static $STATUS_CLOSED = 3;
    public static $STATUS_SPAM = 4;

    public function __construct()
    {
        $this->tableName = "hp_tickets";
    }

    /**
     * Create the stats_buckets table
     *
     * @return PDOStatement
     */
    public function createTable()
    {
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
            `unread` int(1) DEFAULT 0,  /* has unread messages */
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
     * @param string $idSpace space identifier
     * @param string $from mail or name of creator
     * @param string $to mail of destination
     * @param string $subject subject of ticket
     * @param string $body  content of ticket
     * @param array $files  list of CoreFiles
     * @return int id of ticket
     */
    public function createTicket($idSpace, $from, $to, $subject, $body, $idUser=0, $attachments=[])
    {
        // create ticket
        // create message
        // create attachements
        $is_new = false;
        $id_ticket = $this->ticketFromSubject($subject);
        if ($id_ticket == 0) {
            $is_new = true;
            $sql = 'INSERT INTO `hp_tickets` (`id_space`, `status`, `subject`,  `created_by`, `created_by_user`, `created_at`, `unread`)  VALUES (?,?,?, ?,?, NOW(), ?)';
            $this->runRequest($sql, array($idSpace, self::$STATUS_NEW, $subject, $from, $idUser, 1));
            $id_ticket = $this->getDatabase()->lastInsertId();
        }

        $sql = 'INSERT INTO `hp_ticket_message` (`id_ticket`, `from`, `to`, `subject`, `body`, `type`, `created_at`)  VALUES (?,?,?,?,?,?, NOW())';
        $this->runRequest($sql, array($id_ticket, $from, $to, $subject, $body, self::$TYPE_EMAIL));
        $id_message = $this->getDatabase()->lastInsertId();
        if (!$is_new) {
            $this->markUnread($id_ticket);
        }

        if (!empty($attachments)) {
            $this->attach($id_ticket, $id_message, $attachments);
        }
        return ['ticket' => $id_ticket, 'message' => $id_message, 'is_new' => $is_new];
    }

    /**
     * Add a file to a ticket message
     */
    public function attach($id_ticket, $id_message, $files=[])
    {
        $attachments = [];
        foreach ($files as $attachment) {
            $sql = 'INSERT INTO `hp_ticket_attachment` (id_ticket, id_message, id_file, name_file)  VALUES (?,?,?, ?)';
            $this->runRequest($sql, array($id_ticket, $id_message, $attachment['id'], $attachment['name']));
            $attachments[] = $this->getDatabase()->lastInsertId();
        }
        return ['ticket_attachements' => $attachments];
    }

    public function addEmail($id_ticket, $body, $from, $files=[])
    {
        // create message
        $sql = 'INSERT INTO hp_ticket_message (`id_ticket`, `from`, `body`, `type`, created_at, subject)  VALUES (?,?,?,?, NOW(),?)';
        $this->runRequest($sql, array($id_ticket, $from, $body, self::$TYPE_EMAIL, ''));
        $id = $this->getDatabase()->lastInsertId();

        foreach ($files as $attachment) {
            $sql = 'INSERT INTO hp_ticket_attachment (id_ticket, id_message, id_file, name_file)  VALUES (?,?,?,?)';
            $this->runRequest($sql, array($id_ticket, $id, $attachment['id'], $attachment['name']));
        }
        return $id;
    }

    public function addNote($id_ticket, $body, $from)
    {
        // create message
        $sql = 'INSERT INTO hp_ticket_message (`id_ticket`, `from`, `body`, `type`, created_at, subject)  VALUES (?,?,?,?, NOW(),?)';
        $this->runRequest($sql, array($id_ticket, $from, $body, self::$TYPE_NOTE, ''));
        return $this->getDatabase()->lastInsertId();
    }

    public function notify($idSpace, $id_ticket, $lang="en", $isNew=true)
    {
        $cussm = new CoreUserSpaceSettings();
        $ticket = $this->get($id_ticket);

        $subject = "";
        $msg = "";
        $sent = array();

        if ($isNew) {
            $subject = "[Ticket #$id_ticket] new ticket";
            $msg = HelpdeskTranslator::newTicket($lang);
            $users = $cussm->getUsersForSetting($idSpace, "hp_notifyNew", 1);
            foreach ($users as $user) {
                $sent[] = $user["user_id"];
            }
        } else {
            //hp_notifyAssignedUpdate and assigned or hp_notifyAllUpdate
            $subject = "[Ticket #$id_ticket] updated ticket";
            $msg = HelpdeskTranslator::updatedTicket($lang);
            if ($ticket["assigned"]) {
                $userSettings = $cussm->getUserSetting($idSpace, $ticket["assigned"], "hp_notifyAssignedUpdate");
                if ($userSettings) {
                    foreach ($userSettings as $user) {
                        if ($ticket["assigned"] == $user["user_id"] && !in_array($user["user_id"], $sent)) {
                            $sent[] = $ticket["assigned"];
                        }
                    }
                }
            }

            $users = $cussm->getUsersForSetting($idSpace, "hp_notifyAllUpdate", 1);
            foreach ($users as $user) {
                if (!in_array($user["user_id"], $sent)) {
                    $sent[] = $user["user_id"];
                }
            }
        }
        $subject .= " - ".substr($ticket["subject"], 0, 40);
        foreach ($sent as $userToSend) {
            $email = new Email();
            $from = Configuration::get('smtp_from');
            $fromName = "Platform-Manager";
            $cum = new CoreUser();
            $toAddress = $cum->getEmail($userToSend);
            if ($toAddress) {
                $email->sendEmail($from, $fromName, $toAddress, $subject, $msg);
            }
        }
    }

    /**
     * Check subject to see if related to an existing ticket
     *
     * @param string $subject
     * @return int id of ticket, else 0
     */
    public function ticketFromSubject($subject)
    {
        preg_match('/\[Ticket #(\d+)\]/', $subject, $matches, PREG_OFFSET_CAPTURE);
        Configuration::getLogger()->debug('[helpdesk] check if linked to other ticket', ['subject' => $subject, 'matches' => $matches]);
        if (!$matches) {
            return 0;
        }
        $res = $this->get($matches[1][0]);
        if ($res) {
            return $res['id'];
        }
        return 0;
    }

    public function assign($id_ticket, $idUser)
    {
        $um = new CoreUser();
        $login = $um->getUserLogin($idUser);
        $sql = "UPDATE hp_tickets set assigned=?, assigned_name=? WHERE id=?";
        $this->runRequest($sql, array($idUser, $login, $id_ticket));
    }

    public function setStatus($id_ticket, $status, $reminder_date=null)
    {
        if ($status === self::$STATUS_REMINDER) {
            $sql = "UPDATE hp_tickets set `status`=?, reminder=?, reminder_set=0 WHERE id=?";
            $this->runRequest($sql, array($status, $reminder_date, $id_ticket));
        }
        $sql = "UPDATE hp_tickets SET `status`=? WHERE id=?";
        $this->runRequest($sql, array($status, $id_ticket));
    }

    public function list($idSpace, $status=0, $idUser=0, $offset=0, $limit=50)
    {
        $sql = "SELECT * FROM hp_tickets WHERE `status`=? AND id_space=?";
        if ($idUser) {
            $sql .= " AND (assigned=? OR created_by_user=?) ORDER BY id DESC LIMIT ".intval($limit)." OFFSET ".intval($offset);
            return $this->runRequest($sql, array($status, $idSpace, $idUser, $idUser))->fetchAll();
        }
        $sql .= " ORDER BY id DESC LIMIT ".intval($limit)." OFFSET ".intval($offset);
        return $this->runRequest($sql, array($status, $idSpace))->fetchAll();
    }

    public function get($id_ticket)
    {
        $sql = "SELECT * FROM hp_tickets WHERE id=?";
        return $this->runRequest($sql, array($id_ticket))->fetch();
    }

    public function getMessages($id_ticket)
    {
        $sql = "SELECT * FROM hp_ticket_message WHERE id_ticket=?";
        return $this->runRequest($sql, array($id_ticket))->fetchAll();
    }

    public function getMessage($id)
    {
        $sql = "SELECT * FROM hp_ticket_message WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getAttachement($id)
    {
        $sql = "SELECT * FROM hp_ticket_attachment WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getAttachments($id_ticket, $id_message=0)
    {
        if ($id_message) {
            $sql = "SELECT * FROM hp_ticket_attachment WHERE id_ticket=? AND id_message=?";
            return $this->runRequest($sql, array($id_ticket, $id_message))->fetchAll();
        }
        $sql = "SELECT * FROM hp_ticket_attachment WHERE id_ticket=?";
        return $this->runRequest($sql, array($id_ticket))->fetchAll();
    }

    public function move($id_ticket, $queue)
    {
        $sql = "UPDATE hp_tickets SET `queue`=? WHERE id=?";
        $this->runRequest($sql, array($queue, $id_ticket));
    }

    // Delete all tickets in spam status for more than 1 day
    public function trashSpam()
    {
        $sql = "SELECT * FROM hp_tickets WHERE status=? AND updated_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)";
        $spams = $this->runRequest($sql, array(self::$STATUS_SPAM))->fetchAll();
        foreach ($spams as $spam) {
            $this->trash($spam['id']);
        }
    }

    public function trash($id_ticket)
    {
        // TODO delete files too....
        $sql = "SELECT * FROM hp_ticket_attachment WHERE id_ticket=?";
        $files = $this->runRequest($sql, array($id_ticket))->fetchAll();
        $cfm = new CoreFiles();
        if ($files) {
            foreach ($files as $attachment) {
                $cfm->delete($attachment['id_file']);
            }
        }

        $sql = "DELETE FROM hp_ticket_attachment WHERE id_ticket=?";
        $this->runRequest($sql, array($id_ticket));
        $sql = "DELETE FROM hp_ticket_message WHERE id_ticket=?";
        $this->runRequest($sql, array($id_ticket));
        $sql = "DELETE FROM hp_tickets WHERE id=?";
        $this->runRequest($sql, array($id_ticket));
    }

    /**
     * Get helpdesk email for space
     *
     * @param CoreSpace $space current space object
     * @return string email for space helpdesk
     */
    public function fromAddress($space)
    {
        $from = Configuration::get('helpdesk_email', null);
        if (!$from) {
            return null;
        }
        $fromInfo = explode('@', $from);
        return $fromInfo[0]. '+' . $space['shortname'] . '@' . $fromInfo[1];
    }

    public function markRead($id_ticket)
    {
        $sql = "UPDATE hp_tickets SET unread=0 WHERE id=?";
        $this->runRequest($sql, array($id_ticket));
    }

    public function markUnread($id_ticket)
    {
        $sql = "UPDATE hp_tickets SET unread=1 WHERE id=?";
        $this->runRequest($sql, array($id_ticket));
    }

    // count unread messages
    public function unread($idSpace)
    {
        $sql = "SELECT count(*) as total, status FROM hp_tickets WHERE unread=1 AND id_space=? GROUP BY status";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function remind($lang="en")
    {
        $sql = "SELECT * FROM hp_tickets WHERE reminder<NOW() and reminder_sent=0";
        $toRemind = $this->runRequest($sql)->fetchAll();
        foreach ($toRemind as $ticket) {
            if (!$ticket["assigned"]) {
                continue;
            }
            $email = new Email();
            $from = Configuration::get('smtp_from');
            $fromName = "Platform-Manager";
            $cum = new CoreUser();
            $toAddress = $cum->getEmail($ticket["assigned"]);
            if ($toAddress) {
                $subject = "[Ticket #".$ticket["id"]."] reminder reached";
                $msg = HelpdeskTranslator::reminderReachedTicket($lang);
                $email->sendEmail($from, $fromName, $toAddress, $subject, $msg);
                $sql = "UPDATE hp_tickets SET reminder_sent=1 WHERE id=?";
                $this->runRequest($sql, array($ticket["id"]));
            }
        }
    }

    /**
     * count number of tickets per status per space
     */
    public function count($idSpace)
    {
        $sql = "SELECT count(*) as total, status FROM hp_tickets WHERE id_space=? GROUP BY status";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }
}
