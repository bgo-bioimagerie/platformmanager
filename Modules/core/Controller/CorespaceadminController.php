<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/Errors.php';
require_once 'Framework/Utils.php';


require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Framework/FileUpload.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreInstall.php';
require_once 'Modules/core/Model/CoreSpace.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/clients/Controller/ClientsconfigController.php';
require_once 'Modules/resources/Controller/ResourcesconfigController.php';
require_once 'Modules/booking/Controller/BookingconfigController.php';

require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/users/Model/UsersTranslator.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CorespaceadminController extends CoresecureController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            //throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction()
    {
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
        $lang = $this->getLanguage();

        $table = new TableView();
        $table->setTitle(CoreTranslator::Spaces($lang), 3);

        $plans = [];
        foreach (Configuration::get('plans', []) as $plan) {
            $plans[$plan['id']] = $plan['name'];
        }

        $modelSpace = new CoreSpace();
        $data = $modelSpace->getSpaces("name");
        for ($i = 0 ; $i < count($data) ; $i++) {
            $data[$i]["url"] = "corespace/" . $data[$i]["id"];
            $data[$i]['plan_expire'] = $data[$i]['plan_expire'] ? date('Y-m-d', $data[$i]['plan_expire']) : '';
            $data[$i]['plan'] = $plans[$data[$i]['plan']] ?? $data[$i]['plan'];
        }

        $headers = array(
            "name" => CoreTranslator::Name($lang),
            "status" => CoreTranslator::Status($lang),
            "url" => CoreTranslator::Url($lang),
            "plan" => 'Plan',
            "plan_expire" => 'Expiration'
        );

        $table->addLineEditButton("spaceadminedit");
        $table->addDeleteButton("spaceadmindelete");
        $tableHtml = $table->view($data, $headers);

        return $this->render(array("lang" => $lang, "tableHtml" => $tableHtml, "data" => ["spaces" => $data]));
    }

    public function editAction($idSpace)
    {
        // Check user is superadmin or space admin
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $isSuperAdmin = $this->isUserAuthorized(CoreStatus::$ADMIN);

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);
        $lang = $this->getLanguage();

        $formTitle = ($idSpace > 0) ? "Edit_space" : "Create_space";

        $form = new Form($this->request, "corespaceadminedit");
        $form->setTitle(CoreTranslator::$formTitle($lang));

        if (!$space) {
            $space = CoreSpace::new();
            $form->addSelect("preconfigure", CoreTranslator::Preconfigure_space($lang), array(CoreTranslator::no($lang),CoreTranslator::yes($lang)), array(0,1), 0);
        }

        $spaceAdmins = $modelSpace->spaceAdmins($idSpace);

        $form->addText("name", CoreTranslator::Name($lang), true, $space["name"]);
        $form->addSelect("status", CoreTranslator::Status($lang), array(CoreTranslator::PrivateA($lang),CoreTranslator::PublicA($lang)), array(0,1), $space["status"]);
        $form->addColor("color", CoreTranslator::color($lang), false, $space["color"]);
        $form->addColor("txtcolor", CoreTranslator::text_color($lang), false, $space["txtcolor"]);
        $form->addUpload("image", CoreTranslator::Image($lang), $space["image"] ?? null);
        $form->addTextArea("description", CoreTranslator::Description($lang), false, $space["description"]);
        $form->addText("contact", CoreTranslator::Contact($lang), true, $space["contact"]);
        $form->addText("support", CoreTranslator::Support($lang), false, $space["support"]);
        $form->addText("termsofuse", CoreTranslator::Policy($lang), false, $space['termsofuse'] ?? '');

        $modelUser = new CoreUser();
        $users = $modelUser->getActiveUsers("name");
        $usersNames = array();
        $usersIds = array();
        $usersNames[] = CoreTranslator::Select($lang);
        $usersIds[] = 0;
        foreach ($users as $user) {
            $usersNames[] = $user["name"] . " " . $user["firstname"];
            $usersIds[] = $user["id"];
        }

        if ($isSuperAdmin) {
            $configPlans = Configuration::get('plans', []);
            $plans = [];
            $plansIds = [];
            foreach ($configPlans as $p) {
                $plans[] = $p['name'];
                $plansIds[] = $p['id'];
            }
            $form->addSelect('plan', 'Plan', $plans, $plansIds, $space['plan']);
            $expires = $space['plan_expire'];
            if ($expires) {
                $expires = date('Y-m-d', $expires);
            }
        }

        $cc = new CoreConfig();
        $expirationChoices = $cc->getExpirationChoices($lang);
        $choices = $expirationChoices['labels'];
        $choicesid = $expirationChoices['ids'];

        $form->addSelect("user_desactivate", CoreTranslator::Disable_user_account_when($lang), $choices, $choicesid, $space['user_desactivate'] ?? 1);
        $form->addSelect("on_user_deactivate", CoreTranslator::Disable_user_account_on($lang), [CoreTranslator::Disable_Inactivate($lang), CoreTranslator::Disable_Remove($lang)], [CoreConfig::$ONEXPIRE_INACTIVATE, CoreConfig::$ONEXPIRE_REMOVE], $space['on_user_desactivate'] ?? CoreConfig::$ONEXPIRE_INACTIVATE);

        $formAdd = new FormAdd($this->request, "addformspaceedit");
        $formAdd->addSelect("admins", CoreTranslator::Admin($lang), $usersNames, $usersIds, $spaceAdmins);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd, CoreTranslator::Admin($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "spaceadminedit/".$idSpace);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "spaceadmin");

        $id = $idSpace;
        if ($form->check()) {
            $shortname = $this->request->getParameter("name");
            $shortname = strtolower($shortname);
            $shortname = preg_replace('/[^a-z0-9\-_]/', '', $shortname);
            if ($space && $space['shortname']) {
                // Cannot modify shortname once set
                $shortname = $space['shortname'];
            }
            // set base informations
            if ($isSuperAdmin) {
                // Only super admin can create
                Configuration::getLogger()->debug('[admin][space] create/edit space', ["space" => $idSpace, "name" => $this->request->getParameter("name")]);
                $id = $modelSpace->setSpace(
                    $idSpace,
                    $this->request->getParameter("name"),
                    $this->request->getParameter("status"),
                    $this->request->getParameter("color"),
                    $shortname,
                    $this->request->getParameter("contact"),
                    $this->request->getParameter("support"),
                    $this->request->getParameter("txtcolor"),
                );
                $plan = $this->request->getParameterNoException("plan");
                if ($plan !== "") {
                    //plan_expire , plan
                    $expires = $this->request->getParameterNoException("plan_expire");
                    if ($expires) {
                        $expires = Utils::timestamp($expires, $lang);
                    } else {
                        $expires = 0;
                    }
                    $modelSpace->setPlan($id, intval($plan), $expires);
                }

                $planChanged = false;
                if (!$space['id']) {
                    $planChanged = true;
                }
                if (intval($space['plan']) != intval($plan)) {
                    $planChanged = true;
                }
                if ($planChanged) {
                    $event = [
                        "action" => Events::ACTION_PLAN_EDIT,
                        "space" => ["id" => intval($id)],
                        "plan" => ["id" => intval($plan)],
                        "old" => ["id" => intval($space['plan'])]
                    ];
                    Events::send($event);
                }
            } else {
                // Space admin can edit
                Configuration::getLogger()->debug('[admin][space] edit space', ["name" => $this->request->getParameter("name")]);
                $modelSpace->editSpace(
                    $idSpace,
                    $this->request->getParameter("name"),
                    $this->request->getParameter("status"),
                    $this->request->getParameter("color"),
                    $shortname,
                    $this->request->getParameter("contact"),
                    $this->request->getParameter("support"),
                    $this->request->getParameter("txtcolor"),
                );
            }

            $modelSpace->setDescription($id, $this->request->getParameter("description"));
            $modelSpace->setAdmins($id, $this->request->getParameter("admins"));
            $modelSpace->setDeactivate($id, $this->request->getParameter('user_desactivate'));

            $termsofuse = $this->request->getParameterNoException("termsofuse");
            $modelSpace->setTermsOfUse($id, $termsofuse);
            $modelSpace->setOnDeactivate($id, $this->request->getParameter('on_user_deactivate'));

            // upload image
            $target_dir = "data/core/menu/";
            if ($_FILES && $_FILES["image"]["name"] != "") {
                $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);

                $url = $id . "." . $ext;
                FileUpload::uploadFile($target_dir, "image", $url);

                $modelSpace->setImage($id, $target_dir . $url);
            }

            $newSpace = $modelSpace->getSpace($id);

            if ($this->request->getParameterNoException("preconfigure")) {
                $this->preconfigureSpace($newSpace);
            }

            if ($isSuperAdmin) {
                return $this->redirect("spaceadmin", [], ['space' => $newSpace]);
            }
        }

        // set showTodo to true if coming back from a todo action
        $showTodo = ($this->request->getParameterNoException('showTodo') == 1) ? true : false;

        // generate todoList informations
        $todolist = ($idSpace > 0) ? $this->todolist($space['id']) : null;
        return $this->render(
            array(
                "lang" => $lang,
                "formHtml" => $form->getHtml($lang),
                "todolist" => $todolist,
                "showTodo" => $showTodo,
                "data" => ["space" => $space]
            )
        );
    }

    protected function todolist($idSpace)
    {
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();

        $todoData = array();
        $activeModules = array_column($modelSpace->getDistinctSpaceMenusModules($idSpace), 'module');
        $baseModules = array('users', 'resources', 'clients', 'booking');
        array_push($activeModules, 'users');

        foreach ($baseModules as $baseModule) {
            if (in_array($baseModule, $activeModules)) {
                $fName = 'get' . ucFirst($baseModule) . 'Todo';
                $todoData[$baseModule] = $this->$fName($idSpace, $lang);
            }
        }

        $modulesDocUrl = "https://bgo-bioimagerie.github.io/platformmanager/modules/module/";
        foreach (array_keys($todoData) as $module) {
            $todoData[$module]['docurl'] = $modulesDocUrl . lcfirst($todoData[$module]['title']);
            if ($todoData[$module]['title'] != "Users") {
                $todoData[$module] = $this->checkForTasksDone($todoData[$module], $idSpace);
            }
        }
        return $todoData;
    }

    protected function getUsersTodo($idSpace, $lang)
    {
        $modelUser = new CoreUser();
        $modelPending = new CorePendingAccount();
        return
            [
                "title" => "Users",
                "tasks" => [
                    [
                        "id" => "users",
                        "model" => "CoreUser",
                        "title" => UsersTranslator::Create_item("user", $lang),
                        "url" => "corespaceaccessuseradd/" . $idSpace,
                        "done" => $modelUser->countSpaceActiveUsers($idSpace)
                    ],
                    [
                        "id" => "pendingUsers",
                        "model" => "CorePendingAccount",
                        "title" => UsersTranslator::Create_item("pending", $lang),
                        "url" => "corespacependingusers/" . $idSpace,
                        "done" => $modelPending->countActivatedForSpace($idSpace)
                    ],
                ]
            ];
    }

    protected function getResourcesTodo($idSpace, $lang)
    {
        return
            [
                "title" => "Resources",
                "tasks" => [
                    [
                        "id" => "ReArea",
                        "model" => "ReArea",
                        "title" => ResourcesTranslator::Create_item("area", $lang),
                        "url" => "reareasedit/" . $idSpace,
                    ],
                    [
                        "id" => "ReCategory",
                        "model" => "ReCategory",
                        "title" => ResourcesTranslator::Create_item("category", $lang),
                        "url" => "recategoriesedit/" . $idSpace,
                    ],
                    [
                        "id" => "ResourceInfo",
                        "model" => "ResourceInfo",
                        "title" => ResourcesTranslator::Create_item("resource", $lang),
                        "url" => "resourcesedit/" . $idSpace,
                    ],
                    [
                        "id" => "ReVisa",
                        "model" => "ReVisa",
                        "title" => ResourcesTranslator::Create_item("visa", $lang),
                        "url" => "resourceseditvisa/" . $idSpace,
                    ],
                ]
            ];
    }

    protected function getClientsTodo($idSpace, $lang)
    {
        $modelUser = new CoreUser();
        return
            [
                "title" => "Clients",
                "tasks" => [
                    [
                        "id" => "company",
                        "model" => "ClCompany",
                        "title" => ClientsTranslator::Create_item("company", $lang),
                        "url" => "clcompany/" . $idSpace,
                    ],
                    [
                        "id" => "pricings",
                        "model" => "ClPricing",
                        "title" => ClientsTranslator::Create_item("pricing", $lang),
                        "url" => "clpricingedit/" . $idSpace,
                    ],
                    [
                        "id" => "clients",
                        "model" => "ClClient",
                        "title" => ClientsTranslator::Create_item("client", $lang),
                        "url" => "clclientedit/" . $idSpace,
                    ],
                    [
                        "id" => "clientsuser",
                        "model" => "ClClientUser",
                        "title" => ClientsTranslator::Create_item("clientsuser", $lang),
                        "url" => "corespaceuseredit/" . $idSpace,
                        "options" => [
                            "list" => $modelUser->getSpaceActiveUsers($idSpace),
                            "defaultText" => UsersTranslator::User_account($lang)
                        ]
                    ]
                ]
            ];
    }

    protected function getBookingTodo($idSpace, $lang)
    {
        $modelUser = new CoreUser();
        $modelReArea = new ReArea();
        $opt = "(".CoreTranslator::Optional($lang).") ";
        return
            [
                "title" => "Booking",
                "tasks" => [
                    [
                        "id" => "colorcodes",
                        "model" => "BkColorCode",
                        "title" => BookingTranslator::Create_item("colorcode", $lang),
                        "url" => "bookingcolorcodeedit/" . $idSpace,
                    ],
                    [
                        "id" => "schedule",
                        "model" => "BkScheduling",
                        "title" => $opt . BookingTranslator::Create_item("schedule", $lang),
                        "url" => "bookingschedulingedit/" . $idSpace,
                        "options" => [
                            "list" => $modelReArea->getForSpace($idSpace),
                            "defaultText" => ResourcesTranslator::Area($lang)
                        ]
                    ],
                    [
                        "id" => "auth",
                        "model" => "BkAuthorization",
                        "title" => $opt . BookingTranslator::Create_item("authorisations", $lang),
                        "url" => "corespaceuseredit/" . $idSpace,
                        "options" => [
                            "list" => $modelUser->getSpaceActiveUsers($idSpace),
                            "defaultText" => UsersTranslator::User_account($lang)
                        ]
                    ],
                    [
                        "id" => "access",
                        "model" => "BkAccess",
                        "title" => BookingTranslator::Create_item("access", $lang),
                        "url" => "bookingaccessibilities/" . $idSpace,
                    ],
                    [
                        "id" => "booking",
                        "model" => "BkCalendarEntry",
                        "title" => BookingTranslator::Create_item("booking", $lang),
                        "url" => "bookingdayarea/" . $idSpace,
                    ]
                ]
            ];
    }

    protected function checkForTasksDone($moduleTodo, $idSpace)
    {
        for ($i=0; $i < count($moduleTodo['tasks']); $i++) {
            $model = new $moduleTodo['tasks'][$i]['model']();
            $moduleTodo['tasks'][$i]['done'] = $model->admCount($idSpace)['total'];
        }
        return $moduleTodo;
    }

    protected function preconfigureSpace($space)
    {
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }

        $lang = $this->getLanguage();
        // TODO: set modules to activate at preconfiguration in config ?
        $modulesToActivate = array(
            ["name" => "booking", "status" => 2],
            ["name" => "bookingsettings", "status" => 3],
            ["name" => "clients", "status" => 3],
            ["name" => "resources", "status" => 3],
        );

        // activate modules
        foreach ($modulesToActivate as $module) {
            $this->activateModule($module['name'], $module['status'], $space);
        }

        $this->redirect("spaceadminedit/". $space['id']);
        $this->runAction('core', 'edit', ['id_space' => $space['id']]);

        $_SESSION['flash'] = CoreTranslator::Space_preconfigured($lang);
        $_SESSION['flashClass'] = "success";
    }

    protected function activateModule($moduleName, $status, $space)
    {
        $formId = $moduleName . "menusactivationForm";
        $moduleBaseName = strpos($moduleName, 'settings') ? explode("settings", $moduleName)[0] : $moduleName;
        $params = array(
            "path" => $moduleBaseName . "config/".$space['id'],
            "formid" => $formId,
            $moduleName . "Menustatus" => $status,
            $moduleName . "DisplayMenu" => 0,
            $moduleName . "DisplayColor" =>  $space['color'],
            $moduleName . "DisplayColorTxt" => $space['txtcolor']
        );
        $this->request->setParams($params);
        $ctrlName = $moduleBaseName . 'configController';
        $c = new $ctrlName($this->request, $space);
        $c->runAction($moduleBaseName, 'index', ['id_space' => $space['id']]);
    }

    public function deleteAction($idSpace)
    {
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
        $model = new CoreSpace();
        $model->delete($idSpace);
        $this->redirect("spaceadmin");
    }
}
