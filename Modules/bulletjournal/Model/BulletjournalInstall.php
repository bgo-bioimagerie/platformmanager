<?php

require_once 'Framework/Model.php';
require_once 'Modules/bulletjournal/Model/BjCollection.php';
require_once 'Modules/bulletjournal/Model/BjCollectionNote.php';
require_once 'Modules/bulletjournal/Model/BjEvent.php';
require_once 'Modules/bulletjournal/Model/BjNote.php';
require_once 'Modules/bulletjournal/Model/BjTask.php';
require_once 'Modules/bulletjournal/Model/BjTaskHistory.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class BulletjournalInstall extends Model {

    /**
     * Create the core database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase() {        

        $collectionModel = new BjCollection();
        $collectionModel->createTable();
        
        $collectionNoteModel = new BjCollectionNote();
        $collectionNoteModel->createTable();
        
        $eventModel = new BjEvent();
        $eventModel->createTable();
        
        $noteModel = new BjNote();
        $noteModel->createTable();
        
        $taskModel = new BjTask();
        $taskModel->createTable();
        
        $taskHistoryModel = new BjTaskHistory();
        $taskHistoryModel->createTable();
        
        
        if (!file_exists('data/bulletjournal/')) {
            mkdir('data/bulletjournal/', 0777, true);
        }
    }
}
