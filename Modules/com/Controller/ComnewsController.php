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
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("com", $id_space, $_SESSION["id_user"]);
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
        if ($this->role >= CoreSpace::$ADMIN) {
            $table->addLineEditButton("comnewsedit/".$id_space, "id");
            $table->addDeleteButton("comnewsdelete/".$id_space, "id", "title");
        }
        $tableHtml = $table->view($data, $headers);

        return $this->render(array(
            "id_space" => $id_space,
            "tableHtml" => $tableHtml,
            "data" => ["news" => $data]
        ));
    }

    public function notifsAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);
        $modelComNews = new ComNews();
        $news = $modelComNews->getByDate($id_space, 50);
        return $this->render(['data' => ['notifs' => count($news)]]);
    }

    public function editAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("com", $id_space, $_SESSION["id_user"]);
        if ($this->role < CoreSpace::$ADMIN) {
            throw new PfmAuthException('admins only access');
        }
        $lang = $this->getLanguage();

        $modelComNews = new ComNews();
        $data = $modelComNews->get($id_space, $id);

        $form = new Form($this->request, "comneweditform");
        $form->setTitle(ComTranslator::NewsEdit($lang));
        $form->addHidden("id", $data["id"]);
        $form->addText("title", ComTranslator::Title($lang), true, $data["title"]);
        $form->addDate("date", ComTranslator::Date($lang), true, $data["date"]);
        $form->addDate("expire", ComTranslator::Expire($lang), false, $data["expires"]);
        $form->addUpload("media", ComTranslator::Media($lang));
        $form->addTextArea("content", ComTranslator::Content($lang), false, $data["content"], true);

        $form->setValidationButton(CoreTranslator::Ok($lang), "comnewsedit/" . $id_space);
        $form->setColumnsWidth(2, 10);


        if ($form->check()) {
            // edit database
            $id = $this->request->getParameter("id");
            $title = $this->request->getParameter("title");
            $content = $this->request->getParameter("content", false);
            $date = $this->request->getParameter("date");
            $expire = $this->request->getParameter("expire");
            $idNew = $modelComNews->set($id, $id_space, $title, $content, $date, $expire);
            // upload
            $target_dir = "data/com/news/";
            if (isset($_FILES) && isset($_FILES['media']) && $_FILES["media"]["name"] != "") {
                $ext = pathinfo($_FILES["media"]["name"], PATHINFO_BASENAME);
                FileUpload::uploadFile($target_dir, "media", $idNew . "_" . $ext);

                $modelComNews->setMedia($id_space, $idNew, $target_dir . $idNew . "_" . $ext);
            }

            // redirect
            $_SESSION["flash"] = ComTranslator::NewsHasBeenSaved($lang);
            $_SESSION["flashClass"] = "success";
            return $this->redirect('comnews/' . $id_space, [], ['news' => ['id' => $idNew]]);
        }

        return $this->render(array(
            "id_space" => $id_space,
            "formHtml" => $form->getHtml($lang),
            "data" => ["news" => $data]
        ));
    }

    public function deleteAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("com", $id_space, $_SESSION["id_user"]);
        if ($this->role < CoreSpace::$ADMIN) {
            throw new PfmAuthException('admins only access');
        }
        $model = new ComNews();
        $model->delete($id_space, $id);

        return $this->redirect("comnews/" . $id_space);
    }
}
