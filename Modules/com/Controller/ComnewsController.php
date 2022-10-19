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
class ComnewsController extends ComController
{
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("com", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelComNews = new ComNews();
        $data = $modelComNews->getForSpace($idSpace);

        $table = new TableView();
        $table->setTitle(ComTranslator::News($lang));
        $headers = array(
            "title" => ComTranslator::Title($lang),
            "date" => ComTranslator::Date($lang),
            "expires" => ComTranslator::Expire($lang)
        );
        if ($this->role >= CoreSpace::$ADMIN) {
            $table->addLineEditButton("comnewsedit/".$idSpace, "id");
            $table->addDeleteButton("comnewsdelete/".$idSpace, "id", "title");
        }
        $tableHtml = $table->view($data, $headers);

        return $this->render(array(
            "id_space" => $idSpace,
            "tableHtml" => $tableHtml,
            "data" => ["news" => $data]
        ));
    }

    public function notifsAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("helpdesk", $idSpace, $_SESSION["id_user"]);
        $modelComNews = new ComNews();
        $news = $modelComNews->getByDate($idSpace, 50);
        return $this->render(['data' => ['notifs' => count($news)]]);
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("com", $idSpace, $_SESSION["id_user"]);
        if ($this->role < CoreSpace::$ADMIN) {
            throw new PfmAuthException('admins only access');
        }
        $lang = $this->getLanguage();

        $modelComNews = new ComNews();
        $data = $modelComNews->get($idSpace, $id);

        $form = new Form($this->request, "comneweditform");
        $form->setTitle(ComTranslator::NewsEdit($lang));
        $form->addHidden("id", $data["id"]);
        $form->addText("title", ComTranslator::Title($lang), true, $data["title"]);
        $form->addDate("date", ComTranslator::Date($lang), true, $data["date"]);
        $form->addDate("expire", ComTranslator::Expire($lang), false, $data["expires"]);
        $form->addUpload("media", ComTranslator::Media($lang));
        $form->addTextArea("content", ComTranslator::Content($lang), false, $data["content"], true);

        $form->setValidationButton(CoreTranslator::Ok($lang), "comnewsedit/" . $idSpace);
        $form->setColumnsWidth(2, 10);


        if ($form->check()) {
            // edit database
            $id = $this->request->getParameter("id");
            $title = $this->request->getParameter("title");
            $content = $this->request->getParameter("content", false);
            $date = $this->request->getParameter("date");
            $expire = $this->request->getParameter("expire");
            $idNew = $modelComNews->set($id, $idSpace, $title, $content, $date, $expire);
            // upload
            $target_dir = "data/com/news/";
            if (isset($_FILES) && isset($_FILES['media']) && $_FILES["media"]["name"] != "") {
                $ext = pathinfo($_FILES["media"]["name"], PATHINFO_BASENAME);
                FileUpload::uploadFile($target_dir, "media", $idNew . "_" . $ext);

                $modelComNews->setMedia($idSpace, $idNew, $target_dir . $idNew . "_" . $ext);
            }

            // redirect
            $_SESSION["flash"] = ComTranslator::NewsHasBeenSaved($lang);
            $_SESSION["flashClass"] = "success";
            return $this->redirect('comnews/' . $idSpace, [], ['news' => ['id' => $idNew]]);
        }

        return $this->render(array(
            "id_space" => $idSpace,
            "formHtml" => $form->getHtml($lang),
            "data" => ["news" => $data]
        ));
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("com", $idSpace, $_SESSION["id_user"]);
        if ($this->role < CoreSpace::$ADMIN) {
            throw new PfmAuthException('admins only access');
        }
        $model = new ComNews();
        $model->delete($idSpace, $id);

        return $this->redirect("comnews/" . $idSpace);
    }
}
