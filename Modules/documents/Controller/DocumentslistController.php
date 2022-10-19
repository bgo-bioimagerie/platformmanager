<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/documents/Model/DocumentsTranslator.php';
require_once 'Modules/documents/Model/Document.php';
require_once 'Framework/FileUpload.php';
require_once 'Modules/documents/Controller/DocumentsController.php';
require_once 'Modules/clients/Model/ClClientUser.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreConfig.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class DocumentslistController extends DocumentsController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("documents", $idSpace, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        $table = new TableView();

        $modelDoc = new Document();
        $modelCoreConfig = new CoreConfig();
        $editRole = $modelCoreConfig->getParamSpace("documentsEdit", $idSpace, CoreSpace::$MANAGER);
        $data = [];
        if ($this->role >= $editRole) {
            $data = $modelDoc->getForSpace($idSpace);
        } elseif ($this->role == CoreSpace::$USER) {
            $docids = [];
            $docs = $modelDoc->getPublicDocs($idSpace);
            foreach ($docs as $doc) {
                $data[] = $doc;
                $docids[$doc['id']] = true;
            }
            $docs = $modelDoc->getRestrictedDocs($idSpace, Document::$VISIBILITY_MEMBERS);
            foreach ($docs as $doc) {
                if (array_key_exists($doc['id'], $docids)) {
                    continue;
                }
                $data[] = $doc;
            }
            $docs = $modelDoc->getRestrictedDocs($idSpace, Document::$VISIBILITY_USER, $_SESSION['id_user']);
            foreach ($docs as $doc) {
                if (array_key_exists($doc['id'], $docids)) {
                    continue;
                }
                $data[] = $doc;
            }
            $mc = new ClClientUser();
            $clients = $mc->getUserClientAccounts($_SESSION['id_user'], $idSpace);
            foreach ($clients as $client) {
                $docs = $modelDoc->getRestrictedDocs($idSpace, Document::$VISIBILITY_CLIENT, $client['id']);
                foreach ($docs as $doc) {
                    if (array_key_exists($doc['id'], $docids)) {
                        continue;
                    }
                    $data[] = $doc;
                }
            }
        } else {
            $data = $modelDoc->getPublicDocs($idSpace);
        }

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["lastmodified"] = CoreTranslator::dateFromEn($data[$i]["date_modified"], $lang);
            $visibility = 'Private';
            switch ($data[$i]['visibility']) {
                case Document::$VISIBILITY_PUBLIC:
                    $visibility = 'Public';
                    break;
                case Document::$VISIBILITY_MEMBERS:
                    $visibility = 'Members';
                    break;
                case Document::$VISIBILITY_USER:
                    $visibility = 'User ['.$data[$i]['id_ref'].']';
                    break;
                case Document::$VISIBILITY_CLIENT:
                    $visibility = 'Client ['.$data[$i]['id_ref'].']';
                    break;
                case Document::$VISIBILITY_PRIVATE:
                    $visibility = 'Private';
                    break;
                default:
                    $visibility = 'Public';
                    break;
            }
            $data[$i]["visibility"] = $visibility;
        }

        $headers = array(
            "title" => DocumentsTranslator::Title($lang),
            "user" => DocumentsTranslator::Owner($lang),
            "lastmodified" => DocumentsTranslator::LastModified($lang),
        );
        if ($this->role >= $editRole) {
            $headers['visibility'] = 'Visibility';
        }

        $table->addLineButton("documentsopen/" . $idSpace . "/", "id", DocumentsTranslator::Open($lang));
        if ($this->role > CoreSpace::$USER) {
            $table->addLineEditButton("documentsedit/" . $idSpace . "/");
            $table->addDeleteButton("documentsdelete/" . $idSpace . "/", "id", "title");
        }
        $tableView = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "tableHtml" => $tableView,
            "userSpaceStatus" => $this->role,
            "dir" => $this->request->getParameterNoException("dir"),
            "data" => ["documents" => $data]
        ));
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("documents", $idSpace, $_SESSION["id_user"]);
        $modelCoreConfig = new CoreConfig();
        $editRole = $modelCoreConfig->getParamSpace("documentsEdit", $idSpace, CoreSpace::$MANAGER);

        if ($this->role < $editRole) {
            throw new PfmAuthException('not enough privileges');
        }
        $lang = $this->getLanguage();
        $model = new Document();
        $data = $model->get($idSpace, $id);

        $dir = $this->request->getParameterNoException('dir');
        if (!$data['id'] && $dir) {
            if (str_starts_with($dir, '/')) {
                $dir = ltrim($dir, '/');
            }
            $data['title'] = $dir.'/newdocument';
        }

        $form = new Form($this->request, "DocumentsEditAction");
        $form->setTitle(DocumentsTranslator::Edit_Document($lang));
        $form->addText("title", DocumentsTranslator::Title($lang), true, $data["title"]);
        $form->addUpload("file_url", DocumentsTranslator::File($lang));


        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if ($plan->hasFlag(CorePlan::FLAGS_DOCUMENTS)) {
            $form->addSelectMandatory(
                "visibility",
                "Visibility",
                ["Private", "Members", "Protected User", "Protected Client", "Public"],
                [Document::$VISIBILITY_PRIVATE, Document::$VISIBILITY_MEMBERS, Document::$VISIBILITY_USER, Document::$VISIBILITY_CLIENT, Document::$VISIBILITY_PUBLIC],
                $data['visibility'] ?? Document::$VISIBILITY_PRIVATE
            );
            $mu = new CoreUser();
            $users = $mu->getSpaceActiveUsersForSelect($idSpace, "name");
            $mc = new ClClient();
            $clients = $mc->getForList($idSpace);
            $clients['names'] = array_merge([''], $clients['names']);
            $clients['ids'] = array_merge([0], $clients['ids']);
            $form->addSelect('id_ref_user', CoreTranslator::User($lang), $users['names'], $users['ids'], $data['id_ref'] ?? 0);
            $form->addSelect('id_ref_client', ClientsTranslator::Client($lang), $clients['names'], $clients['ids'], $data['id_ref'] ?? 0);
        } else {
            $form->addHidden('visibility', Document::$VISIBILITY_PRIVATE);
        }


        $form->setValidationButton(CoreTranslator::Save($lang), "documentsedit/" . $idSpace . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "documents/" . $idSpace. '?dir='.$dir);

        if ($form->check()) {
            $title = $this->request->getParameter("title");
            if (str_starts_with($title, '/')) {
                $title = ltrim($title, '/');
            }
            $idUser = $_SESSION["id_user"];
            $idNew = $model->set($id, $idSpace, $title, $idUser);
            $target_dir = "data/documents/";
            if ($_FILES["file_url"]["name"] != "") {
                $ext = pathinfo($_FILES["file_url"]["name"], PATHINFO_BASENAME);
                FileUpload::uploadFile($target_dir, "file_url", $idNew . "_" . $ext);
                $model->setUrl($idSpace, $idNew, $target_dir . $idNew . "_" . $ext);
            }

            $visibility = Document::$VISIBILITY_PRIVATE;
            $id_ref = 0;

            $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
            if ($plan->hasFlag(CorePlan::FLAGS_DOCUMENTS)) {
                $visibility = $this->request->getParameter("visibility");
                if ($visibility == Document::$VISIBILITY_USER) {
                    $id_ref = $this->request->getParameter('id_ref_user');
                } elseif ($visibility == Document::$VISIBILITY_CLIENT) {
                    $id_ref = $this->request->getParameter('id_ref_client');
                }
            }
            $model->setVisibility($idSpace, $idNew, $visibility, $id_ref);

            $this->redirect("documents/" . $idSpace);
        }

        $formHtml = $form->getHtml($lang);
        $this->render(array("id_space" => $idSpace, "lang" => $lang, "formHtml" => $formHtml));
    }

    public function openAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("documents", $idSpace, $_SESSION["id_user"]);

        $model = new Document();
        $doc = $model->get($idSpace, $id);
        switch ($doc['visibility']) {
            case Document::$VISIBILITY_PUBLIC:
                break;
            case Document::$VISIBILITY_MEMBERS:
                if (!$this->role >= CoreSpace::$USER) {
                    throw new PfmAuthException('private document');
                }
                break;
            case Document::$VISIBILITY_PRIVATE:
                if (!$this->role >= CoreSpace::$MANAGER) {
                    throw new PfmAuthException('private document');
                }
                break;
            case Document::$VISIBILITY_USER:
                if ($_SESSION['id_user'] != $doc['id_ref']) {
                    throw new PfmAuthException('private document');
                }
                // no break
            case Document::$VISIBILITY_CLIENT:
                $m = new ClClientUser();
                $clients = $m->getUserClientAccounts($_SESSION['id_user'], $idSpace);
                $isClient = false;
                foreach ($clients as $client) {
                    if ($client['id'] == $doc['id_ref']) {
                        $isClient = true;
                        break;
                    }
                }
                if (!$isClient) {
                    throw new PfmAuthException('private document');
                }
                break;
            default:
                throw new PfmAuthException('private document');
        }
        //$file = $model->getUrl($idSpace,$id);
        $file = $doc['url'];
        if (file_exists($file)) {
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header("Content-Type: binary/octet-stream");
            header("Content-Transfer-Encoding: binary");
            // read the file from disk
            readfile($file);
        } else {
            $_SESSION['flash'] = DocumentsTranslator::Missing_Document($this->getLanguage());
            $_SESSION['flashClass'] = "warning";
            $this->redirect("documents/" . $idSpace);
        }
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("documents", $idSpace, $_SESSION["id_user"]);
        $modelCoreConfig = new CoreConfig();
        $editRole = $modelCoreConfig->getParamSpace("documentsEdit", $idSpace, CoreSpace::$MANAGER);
        if ($this->role < $editRole) {
            throw new PfmAuthException('not enough privileges');
        }
        $model = new Document();
        $model->delete($idSpace, $id);

        $this->redirect("documents/" . $idSpace);
    }
}
