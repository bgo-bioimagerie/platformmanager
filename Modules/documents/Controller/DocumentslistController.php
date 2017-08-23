<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/documents/Model/DocumentsTranslator.php';
require_once 'Modules/documents/Model/Document.php';
require_once 'Framework/FileUpload.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class DocumentslistController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->checkAuthorizationMenu("documents");
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("documents", $id_space, $_SESSION["id_user"]);
        $userSpaceStatus = $this->getUserSpaceStatus($id_space, $_SESSION["id_user"]);
        
        $lang = $this->getLanguage();
        
        $table = new TableView();
        
        $modelDoc = new Document();
        $data = $modelDoc->getForSpace($id_space);
        //print_r($data);
        $modelUser = new CoreUser();
        for($i = 0 ; $i < count($data) ; $i++){
            $data[$i]["user"] = $modelUser->getUserFUllName($data[$i]["id_user"]);
            $data[$i]["lastmodified"] = CoreTranslator::dateFromEn($data[$i]["date_modified"], $lang);
        }
        
        $headers = array("title" => DocumentsTranslator::Title($lang), 
            "user" => DocumentsTranslator::Owner($lang),
            "lastmodified" => DocumentsTranslator::LastModified($lang)
        );
        
        if ($userSpaceStatus >= 3){
            $table->addLineButton("documentsopen/".$id_space."/", "id", DocumentsTranslator::Open($lang));
        }
        $table->addLineEditButton("documentsedit/".$id_space."/");
        $table->addDeleteButton("documentsdelete/".$id_space."/", "id", "title");
        $tableView = $table->view($data, $headers);
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableView, "userSpaceStatus" => $userSpaceStatus));
    }
    
    public function editAction($id_space, $id){
        
        $lang = $this->getLanguage();
        
        $model = new Document();
        $data = $model->get($id);
        
        $form = new Form($this->request, "DocumentsEditAction");
        $form->setTitle(DocumentsTranslator::Edit_Document($lang));
        $form->addText("title", DocumentsTranslator::Title($lang), true, $data["title"]);
        $form->addUpload("file_url", DocumentsTranslator::File($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "documentsedit/".$id_space);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "documents/".$id_space);
        
        if ($form->check()){
            $title = $this->request->getParameter("title");
            $id_user = $_SESSION["id_user"];
            $idNew = $model->set($id, $id_space, $title, $id_user);
            
            $target_dir = "data/documents/";
            if ($_FILES["file_url"]["name"] != "") {
                $ext = pathinfo($_FILES["file_url"]["name"], PATHINFO_BASENAME);
                FileUpload::uploadFile($target_dir, "file_url", $idNew . "_" . $ext);

                $model->setUrl($idNew, $target_dir . $idNew . "_" . $ext);
            }

            $this->redirect("documents/" . $id_space);
        }
        
        $formHtml = $form->getHtml($lang);
        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $formHtml));
    }
    
    public function openAction($id_space, $id){
        
        $this->checkAuthorizationMenuSpace("documents", $id_space, $_SESSION["id_user"]);

        $model = new Document();
        $path = $model->getUrl($id);
        $this->redirect($path);
    }
    
    public function deleteAction($id_space, $id){
        $this->checkAuthorizationMenuSpace("documents", $id_space, $_SESSION["id_user"]);
        $model = new Document();
        $model->delete($id);
        
        $this->redirect("documents/" . $id_space);
    }
}
