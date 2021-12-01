<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/com/Model/ComTranslator.php';
require_once 'Modules/com/Model/ComNews.php';
require_once 'Modules/com/Controller/ComController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ComnewsController extends ComController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("com");
    }

    public function indexAction($id_space) {

        //$this->checkAuthorizationMenuSpace("com", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelComNews = new ComNews();
        $data = $modelComNews->getForSpace($id_space);

        $table = new TableView();
        $table->setTitle(ComTranslator::News($lang));
        $headers = array(
            "title" => ComTranslator::Title($lang),
            "date" => ComTranslator::Date($lang),
            "expires" => ComTranslator::Expire($lang)
        );
        for($i = 0 ; $i < count($data) ; $i++){
            $data[$i]["date"] = CoreTranslator::dateFromEn($data[$i]["date"], $lang); 
            $data[$i]["expires"] = CoreTranslator::dateFromEn($data[$i]["expires"], $lang); 
        }

        $table->addLineEditButton("comnewsedit/".$id_space, "id");
        $table->addDeleteButton("comnewsdelete/".$id_space, "id", "title");
        $tableHtml = $table->view($data, $headers);

        return $this->render(array("id_space" => $id_space, "tableHtml" => $tableHtml));
    }

    public function editAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("com", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelComNews = new ComNews();
        $data = $modelComNews->get($id_space, $id);

        $form = new Form($this->request, "comneweditform");
        $form->setTitle(ComTranslator::NewsEdit($lang));
        $form->addHidden("id", $data["id"]);
        $form->addText("title", ComTranslator::Title($lang), true, $data["title"]);
        $form->addDate("date", ComTranslator::Date($lang), true, CoreTranslator::dateFromEn($data["date"], $lang));
        $form->addDate("expire", ComTranslator::Date($lang), false, CoreTranslator::dateFromEn($data["expires"], $lang));
        $form->addUpload("media", ComTranslator::Media($lang));
        $form->addTextArea("content", ComTranslator::Content($lang), false, $data["content"], true);

        $form->setValidationButton(CoreTranslator::Ok($lang), "comnewsedit/" . $id_space);
        $form->setColumnsWidth(2, 10);
        $form->setButtonsWidth(1, 11);

        if ($form->check()) {
            // edit database
            $id = $this->request->getParameter("id");
            $title = $this->request->getParameter("title");
            $content = $this->request->getParameter("content", false);
            $date = CoreTranslator::dateToEn($this->request->getParameter("date"), $lang);
            $expire = CoreTranslator::dateToEn($this->request->getParameter("expire"), $lang);

            $idNew = $modelComNews->set($id, $id_space, $title, $content, $date, $expire);

            // upload
            $target_dir = "data/com/news/";
            if ($_FILES["media"]["name"] != "") {
                $ext = pathinfo($_FILES["media"]["name"], PATHINFO_BASENAME);
                FileUpload::uploadFile($target_dir, "media", $idNew . "_" . $ext);

                $modelComNews->setMedia($id_space, $idNew, $target_dir . $idNew . "_" . $ext);
            }

            // redirect
            $_SESSION["message"] = ComTranslator::NewsHasBeenSaved($lang);
            $this->redirect('comnewsedit/' . $id_space . "/" . $idNew);
        }

        $this->render(array("id_space" => $id_space, "formHtml" => $form->getHtml($lang)));
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("com", $id_space, $_SESSION["id_user"]);
        $model = new ComNews();
        $model->delete($id_space, $id);

        $this->redirect("comnews/" . $id_space);
    }

}
