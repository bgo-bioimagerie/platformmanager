<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Errors.php';


require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreMainMenu.php';
require_once 'Modules/core/Model/CoreMainSubMenu.php';
require_once 'Modules/core/Model/CoreMainMenuItem.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CoremainmenuController extends CoresecureController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

    public function indexAction()
    {
        $lang = $this->getLanguage();

        $modelMenu = new CoreMainMenu();
        $menus = $modelMenu->getAll();

        $table = new TableView();
        $table->setTitle(CoreTranslator::Menus($lang));
        $headers = array(
            "name" => CoreTranslator::Name($lang),
            "display_order" => CoreTranslator::Display_order($lang),
            "id" => "ID"
        );
        $table->addLineEditButton("coremainmenuedit");
        $table->addDeleteButton("coremainmenudelete");
        $tableHtml = $table->view($menus, $headers);

        return $this->render(array(
            "lang" => $lang,
            "tableHtml" => $tableHtml,
            "data" => ["menus" => $menus]
        ));
    }

    /**
     *
     * @param int $id
     */
    public function editAction($id)
    {
        $lang = $this->getLanguage();

        $modelMenu = new CoreMainMenu();
        $value = $modelMenu->get($id);
        if (!$value) {
            $value = ["name" => "", "display_order" => "0"];
        }

        $form = new Form($this->request, "editmainmenuform");
        $form->setTitle(CoreTranslator::EditMainMenu($lang));
        $form->addText("name", CoreTranslator::Name($lang), true, $value["name"]);
        $form->addNumber("display_order", CoreTranslator::Display_order($lang), true, $value["display_order"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "coremainmenuedit/" . $id);

        if ($form->check()) {
            $menuid = $modelMenu->set($id, $form->getParameter("name"), $form->getParameter("display_order"));
            $_SESSION['flash'] = CoreTranslator::MenuSaved($lang);
            $_SESSION["flashClass"] = 'success';
            $this->redirect("coremainmenus", array(), ['menu' => ['id' => $menuid, 'name' => $form->getParameter("name")]]);
        }

        return $this->render(array(
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang),
        ));
    }

    /**
     *
     * @param int $id
     */
    public function deleteAction($id)
    {
        $model = new CoreMainMenu();
        $model->delete($id);

        $this->redirect("coremainmenus");
    }

    public function submenusAction()
    {
        $lang = $this->getLanguage();

        $modelMenu = new CoreMainSubMenu();
        $menus = $modelMenu->getAll();

        $table = new TableView();
        $table->setTitle(CoreTranslator::SubMenus($lang));
        $headers = array(
            "name" => CoreTranslator::Name($lang),
            "main_menu" => CoreTranslator::MainMenu($lang),
            "display_order" => CoreTranslator::Display_order($lang),
            "id" => "ID"
        );
        $table->addLineEditButton("coremainsubmenuedit", "id");
        $table->addDeleteButton("coremainsubmenudelete", "id", "name");
        $tableHtml = $table->view($menus, $headers);

        return $this->render(array(
            "lang" => $lang,
            "tableHtml" => $tableHtml,
            "data" => ["menus" => $menus]
        ));
    }

    public function submenueditAction($id)
    {
        $lang = $this->getLanguage();

        $modelMenu = new CoreMainSubMenu();
        $value = $modelMenu->get($id);
        if (!$value) {
            $value = ["name" => "", "display_order" => "0", "id_main_menu" => "0"];
        }

        $modelMainMenus = new CoreMainMenu();
        $mainMenus = $modelMainMenus->getForList();

        $form = new Form($this->request, "editmainsubmenuform");
        $form->setTitle(CoreTranslator::EditMainSubMenu($lang));
        $form->addText("name", CoreTranslator::Name($lang), true, $value["name"]);
        $form->addSelect("id_main_menu", CoreTranslator::MainMenu($lang), $mainMenus["names"], $mainMenus["ids"], $value["id_main_menu"]);
        $form->addNumber("display_order", CoreTranslator::Display_order($lang), true, $value["display_order"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "coremainsubmenuedit/" . $id);

        if ($form->check()) {
            $menuid = $modelMenu->set(
                $id,
                $form->getParameter("name"),
                $form->getParameter("id_main_menu"),
                $form->getParameter("display_order")
            );
            $_SESSION['flash'] = CoreTranslator::MenuSaved($lang);
            $_SESSION["flashClass"] = 'success';
            $this->redirect("coremainsubmenus", array(), ['menu' => ['id' => $menuid, 'name' => $form->getParameter("name"), 'id_main_menu' => $form->getParameter("id_main_menu")]]);

            return;
        }

        return $this->render(array(
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang),
        ));
    }

    public function submenudeleteAction($id)
    {
        $model = new CoreMainSubMenu();
        $model->delete($id);

        $this->redirect("coremainsubmenus");
    }

    public function itemsAction()
    {
        $lang = $this->getLanguage();



        $modelItems = new CoreMainMenuItem();
        $items = $modelItems->getAll();

        $table = new TableView();
        $table->setTitle(CoreTranslator::Items($lang));
        $headers = array(
            "main_menu" => CoreTranslator::MainMenu($lang),
            "sub_menu" => CoreTranslator::SubMenu($lang),
            "name" => CoreTranslator::Item($lang),
            "display_order" => CoreTranslator::Display_order($lang),
            "id" => "ID"
        );
        $table->addLineEditButton("coremainmenuitemedit", "id");
        $table->addDeleteButton("coremainmenuitemdelete", "id", "name");
        $tableHtml = $table->view($items, $headers);

        return $this->render(array(
            "lang" => $lang,
            "tableHtml" => $tableHtml,
            "data" => ["menus" => $items]
        ));
    }

    public function itemeditAction($id)
    {
        $lang = $this->getLanguage();

        $modelItem = new CoreMainMenuItem();
        $item = $modelItem->get($id);

        $modelSpace = new CoreSpace();
        $spaceList = $modelSpace->getForList();

        if (!$item) {
            $item = [
                "name" => "",
                "display_order" => "0",
                "id_sub_menu" => "0",
                "id_space" => "0"
            ];
            if ($spaceList && count($spaceList['ids']) > 1) {
                $item['id_space'] = $spaceList['ids'][1];
            }
        }

        $modelSubMenu = new CoreMainSubMenu();
        $subMenuList = $modelSubMenu->getForList();

        $form = new Form($this->request, "editmenuitemform");
        $form->setTitle(CoreTranslator::EditItem($lang));
        $form->addText("name", CoreTranslator::Name($lang), true, $item["name"]);
        $form->addSelect("id_sub_menu", CoreTranslator::SubMenu($lang), $subMenuList["names"], $subMenuList["ids"], $item["id_sub_menu"]);
        $form->addSelect("id_space", CoreTranslator::Space($lang), $spaceList["names"], $spaceList["ids"], $item["id_space"]);
        $form->addNumber("display_order", CoreTranslator::Display_order($lang), true, $item["display_order"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "coremainmenuitemedit/".$id);

        if ($form->check()) {
            if (!$form->getParameter("id_space")) {
                throw new PfmParamException("Invalid space parameter");
            }
            $itemid = $modelItem->set(
                $id,
                $form->getParameter("name"),
                $form->getParameter("id_sub_menu"),
                $form->getParameter("id_space"),
                $form->getParameter("display_order")
            );
            $_SESSION['flash'] = CoreTranslator::MenuSaved($lang);
            $_SESSION["flashClass"] = 'success';
            $this->redirect("coremainmenuitems", array(), ['menu' => ['id' => $itemid, 'name' => $form->getParameter("name"), 'id_space' => $form->getParameter("id_space"), 'id_sub_menu' => $form->getParameter("id_sub_menu")]]);
        }

        return $this->render(array(
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang),
        ));
    }

    /**
     *
     * @param int $id
     */
    public function itemdeleteAction($id)
    {
        $model = new CoreMainMenuItem();
        $model->delete($id);

        $this->redirect("coremainmenuitems");
    }
}
