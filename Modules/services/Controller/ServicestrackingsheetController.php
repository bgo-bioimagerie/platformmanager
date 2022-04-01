<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';

require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/SeOrigin.php';
require_once 'Modules/services/Model/SeVisa.php';
require_once 'Modules/services/Model/SeTrackingsheet.php';

require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/services/Controller/ServicesController.php';

class ServicestrackingsheetController extends ServicesController {

    // TODO: [tracking] add tracking_sheet template
        // => create form
        // => generate .pdf / .doc => afficher l'état des tâches à tout moment dans le projet
    // TODO: [tracking] is adding a digital signature possible?
    // => bouton "j'approuve" suffisant ? => ajouter "signé à telle date" sur pdf => envoyer lien à chacun => le plus simple: on génère un pdf qu'ils signent à la main
    // TODO: [tracking] New gantt system (full js?) de chaque projet. Fonction premium Kanboard vuejs
    // TODO: ajouter tâches (journal) (avec dates (début, clôture), commentaires => on peut y copier le contenu des mails => apparaîtrait dans le document)
    // => mode canboard

    // dans nun second tyemps)
    // images => limiter taille ?
    // intégrer documents dans dossier spécifique au projet
    // dans une tâche on pêut rajouter document
    // au niveau général du projet on peut rajouter document

    // OU un seul dossier par projet => classe corefiles

    public function indexAction($id_space) {

    }

}