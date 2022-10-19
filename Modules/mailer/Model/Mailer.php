<?php

require_once 'Framework/Configuration.php';
require_once 'Framework/Model.php';
require_once 'Framework/Email.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreFiles.php';
require_once 'Modules/core/Model/CoreUserSpaceSettings.php';

require_once 'Modules/helpdesk/Model/HelpdeskTranslator.php';

class Mailer extends Model
{
    public static $SPACE_MEMBERS = 1;
    public static $SPACE_MANAGERS = 2;
    public static $SPACES_ADMINS = 3;

    public function __construct(
        public int $idSpace=0,
        public string $subject='',
        public string $message='',
        public int $type=0
    ) {
        $this->tableName = 'mailer_mails';
    }

    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `mailer_mails` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_space` int(11) NOT NULL,
            `subject` varchar(200) NOT NULL,
            `message` text NOT NULL,
            `type` int,  # type of mail (all space members, space managers, ...)
            PRIMARY KEY (`id`)
            );";
        $this->runRequest($sql);
    }

    public function create(int $idSpace, string $subject, string $message, int $type)
    {
        $sql = "INSERT INTO mailer_mails (id_space, subject, message, type) VALUES (?, ?, ?, ?)";
        $this->runRequest($sql, [$idSpace, $subject, $message, $type]);
        return $this->getDatabase()->lastInsertId();
    }

    public function delete(int $idSpace, int $id)
    {
        $sql = "DELETE FROM mailer_mails WHERE id_space=? AND id=?";
        $this->runRequest($sql, [$idSpace, $id]);
    }

    public function getMails($idSpace, $type=0)
    {
        $sql = "SELECT * FROM mailer_mails WHERE id_space=?";
        $params = array($idSpace);
        if ($type) {
            $params[] = $type;
            $sql .= " AND type=?";
        }
        $sql .=" ORDER BY created_at DESC LIMIT 200";
        return $this->runRequest($sql, $params)->fetchAll();
    }

    /**
     * count number of tickets per status per space
     */
    public function count($idSpace)
    {
        $sql = "SELECT count(*) as total FROM mailer_mails WHERE id_space=?";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function recent($idSpace, $type=1)
    {
        $sql = "SELECT count(*) as total FROM mailer_mails WHERE id_space=? AND type<=? AND created_at > now() - INTERVAL 7 DAY";
        return $this->runRequest($sql, array($idSpace, $type))->fetch();
    }
}
