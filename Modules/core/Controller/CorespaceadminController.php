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
class CorespaceadminController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            //throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {
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
        for($i = 0 ; $i < count($data) ; $i++){
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
    
    public function editAction($id_space){
        // Check user is superadmin or space admin
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $isSuperAdmin = $this->isUserAuthorized(CoreStatus::$ADMIN);

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);
        $lang = $this->getLanguage();

        $formTitle = ($id_space > 0) ? "Edit_space" : "Create_space";

        $form = new Form($this->request, "corespaceadminedit");
        $form->setTitle(CoreTranslator::$formTitle($lang));

        if(!$space) {
            $space = CoreSpace::new();
            $form->addSelect("preconfigure", CoreTranslator::Preconfigure_space($lang), array(CoreTranslator::no($lang),CoreTranslator::yes($lang)), array(0,1), 0);
        }

        $spaceAdmins = $modelSpace->spaceAdmins($id_space);
        
        $form->addText("name", CoreTranslator::Name($lang), true, $space["name"]);
        $form->addSelect("status", CoreTranslator::Status($lang), array(CoreTranslator::PrivateA($lang),CoreTranslator::PublicA($lang)), array(0,1), $space["status"]);
        $form->addColor("color", CoreTranslator::color($lang), false, $space["color"]);
        $form->addColor("txtcolor", CoreTranslator::text_color($lang), false, $space["txtcolor"]);
        $form->addUpload("image", CoreTranslator::Image($lang), $space["image"] ?? null);
        $form->addTextArea("description", CoreTranslator::Description($lang), false, $space["description"]);
        $form->addText("contact", CoreTranslator::Contact($lang), true, $space["contact"]);
        $form->addText("support", CoreTranslator::Support($lang), false, $space["support"]);

        $modelUser = new CoreUser();
        $users = $modelUser->getActiveUsers("name");
        $usersNames = array();
        $usersIds = array();
        $usersNames[] = CoreTranslator::Select($lang);
        $usersIds[] = 0;
        foreach($users as $user){
            $usersNames[] = $user["name"] . " " . $user["firstname"];
            $usersIds[] = $user["id"];
        }

        if($isSuperAdmin) {
            $configPlans = Configuration::get('plans', []);
            $plans = [];
            $plansIds = [];
            foreach($configPlans as $p) {
                $plans[] = $p['name'];
                $plansIds[] = $p['id'];
            }
            $form->addSelect('plan', 'Plan', $plans, $plansIds, $space['plan']);
            $expires = $space['plan_expire'];
            if($expires) {
                $expires = date('Y-m-d', $expires);
            }
        }

        $cc = new CoreConfig();
        $expirationChoices = $cc->getExpirationChoices($lang);
        $choices = $expirationChoices['labels'];
        $choicesid = $expirationChoices['ids'];

        $form->addSelect("user_desactivate", CoreTranslator::Disable_user_account_when($lang), $choices, $choicesid, $space['user_desactivate'] ?? 1);
        
        $formAdd = new FormAdd($this->request, "addformspaceedit");
        $formAdd->addSelect("admins", CoreTranslator::Admin($lang), $usersNames, $usersIds, $spaceAdmins);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd, CoreTranslator::Admin($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "spaceadminedit/".$id_space);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "spaceadmin");

        $id = $id_space;
        if ($form->check()){
            $shortname = $this->request->getParameter("name");
            $shortname = strtolower($shortname);
            $shortname = preg_replace('/[^a-z0-9\-_]/', '', $shortname);
            if($space && $space['shortname']) {
                // Cannot modify shortname once set
                $shortname = $space['shortname'];
            }
            // set base informations
            if($isSuperAdmin) {
                // Only super admin can create
                Configuration::getLogger()->debug('[admin][space] create/edit space', ["space" => $id_space, "name" => $this->request->getParameter("name")]);
                $id = $modelSpace->setSpace($id_space, $this->request->getParameter("name"), 
                    $this->request->getParameter("status"),
                    $this->request->getParameter("color"),
                    $shortname,
                    $this->request->getParameter("contact"),
                    $this->request->getParameter("support"),
                    $this->request->getParameter("txtcolor"),
                );
                $plan = $this->request->getParameterNoException("plan");
                if($plan !== "") {
                    //plan_expire , plan
                    $expires = $this->request->getParameterNoException("plan_expire");
                    if($expires) {
                        $expires = Utils::timestamp($expires, $lang);

                    } else {
                        $expires = 0;
                    }
                    $modelSpace->setPlan($id, intval($plan), $expires);
                }

                $planChanged = false;
                if(!$space['id']) {
                    $planChanged = true;
                }
                if(intval($space['plan']) != intval($plan)) {
                    $planChanged = true;
                }
                if($planChanged) {
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
                $modelSpace->editSpace($id_space, $this->request->getParameter("name"), 
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

            if($isSuperAdmin) {
                return $this->redirect("spaceadmin", [], ['space' => $newSpace]);
            }
        }

         // set showTodo to true if coming back from a todo action
         $showTodo = ($this->request->getParameterNoException('showTodo') == 1) ? true : false;

         // get flash messages brought back from todoList actions
         $flash = $this->request->getParameterNoException('flash');
         if ($flash) {
             $_SESSION['flash'] = $flash;
             $flashClass = $this->request->getParameterNoException('flashClass');
             if ($flashClass) {
                 $_SESSION['flashClass'] = $flashClass;
             }
         }
 
        // generate todoList informations
        $todolist = ($id_space > 0) ? $this->todolist($space['id']) : null;
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

    protected function todolist($id_space) {
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();

        $todoData = array();
        $activeModules = array_column($modelSpace->getDistinctSpaceMenusModules($id_space), 'module');
        $baseModules = array('users', 'resources', 'clients', 'booking');
        array_push($activeModules, 'users');

        foreach($baseModules as $baseModule) {
            if (in_array($baseModule, $activeModules)) {
                $fName = 'get' . ucFirst($baseModule) . 'Todo'; 
                $todoData[$baseModule] = $this->$fName($id_space, $lang);
            }
        }

        // set documentation urls
        $modulesDocUrl = "https://bgo-bioimagerie.github.io/platformmanager/modules/module/";
        foreach(array_keys($todoData) as $module) {
            $todoData[$module]['docurl'] = $modulesDocUrl . lcfirst($todoData[$module]['title']);
        }
        $todoData['redirectUrl'] = '?redirect=todo';
        return $todoData;
    }

    protected function getUsersTodo($id_space, $lang) {
        $modelUser = new CoreUser();
        $modelPending = new CorePendingAccount();
        return
            [
                "title" => "Users",
                "tasks" => [
                    [
                        "id" => "users",
                        "title" => UsersTranslator::Create_item("user", $lang),
                        "url" => "corespaceaccessuseradd/" . $id_space,
                        "done" => $modelUser->getSpaceActiveUsers($id_space)
                    ],
                    [
                        "id" => "pendingUsers",
                        "title" => UsersTranslator::Create_item("pending", $lang),
                        "url" => "corespacependingusers/" . $id_space,
                        "done" => $modelPending->getActivatedForSpace($id_space)
                    ],
                ]
            ];
    }

    protected function getResourcesTodo($id_space, $lang) {
        $modelArea = new ReArea();
        $modelCategory = new ReCategory();
        $modelResource = new ResourceInfo();
        $modelVisa = new ReVisa();
        
        return
            [
                "title" => "Resources",
                "tasks" => [
                    [
                        "id" => "area",
                        "title" => ResourcesTranslator::Create_item("area", $lang),
                        "url" => "reareasedit/" . $id_space,
                        "done" => $modelArea->getForSpace($id_space)
                    ],
                    [
                        "id" => "category",
                        "title" => ResourcesTranslator::Create_item("category", $lang),
                        "url" => "recategoriesedit/" . $id_space,
                        "done" => $modelCategory->getBySpace($id_space)
                    ],
                    [
                        "id" => "resource",
                        "title" => ResourcesTranslator::Create_item("resource", $lang),
                        "url" => "resourcesedit/" . $id_space,
                        "done" => $modelResource->getForSpace($id_space)
                    ],
                    [
                        "id" => "visa",
                        "title" => ResourcesTranslator::Create_item("visa", $lang),
                        "url" => "resourceseditvisa/" . $id_space,
                        "done" => $modelVisa->getForSpace($id_space)
                    ],
                ]
            ];
    }

    protected function getClientsTodo($id_space, $lang) {
        $modelCompany = new ClCompany();
        $modelPricing = new ClPricing();
        $modelClient = new ClClient();
        $modelClientsuser = new ClClientUser();
        $modelUser = new CoreUser();

        return
            [
                "title" => "Clients",
                "tasks" => [
                    [
                        "id" => "company",
                        "title" => ClientsTranslator::Create_item("company", $lang),
                        "url" => "clcompany/" . $id_space,
                        "done" => $modelCompany->getForSpace($id_space)
                    ],
                    [
                        "id" => "pricing",
                        "title" => ClientsTranslator::Create_item("pricing", $lang),
                        "url" => "clpricingedit/" . $id_space,
                        "done" => !empty($modelPricing->getForList($id_space)['ids'])
                    ],
                    [
                        "id" => "client",
                        "title" => ClientsTranslator::Create_item("client", $lang),
                        "url" => "clclientedit/" . $id_space,
                        "done" => !empty($modelClient->getForList($id_space)['ids'])
                    ],
                    [
                        "id" => "clientsuser",
                        "title" => ClientsTranslator::Create_item("clientsuser", $lang),
                        "url" => "corespaceuseredit/" . $id_space,
                        "done" => $modelClientsuser->getForSpace($id_space),
                        "options" => [
                            "list" => $modelUser->getSpaceActiveUsers($id_space),
                            "defaultText" => UsersTranslator::User_account($lang)
                        ]
                    ]
                ]
            ];
    }

    protected function getBookingTodo($id_space, $lang) {
        $modelBkEntry = new BkCalendarEntry();
        $modelColor = new BkColorCode();
        $modelSchedule = new BkScheduling();
        $modelBkAccess = new BkAccess();
        $modelBkAuth = new BkAuthorization();
        $modelUser = new CoreUser();
        $opt = "(".CoreTranslator::Optional($lang).") ";

        return
            [
                "title" => "Booking",
                "tasks" => [
                    [
                        "id" => "colorcodes",
                        "title" => BookingTranslator::Create_item("colorcode", $lang),
                        "url" => "bookingcolorcodeedit/" . $id_space,
                        "done" => $modelColor->getForSpace($id_space)
                    ],
                    [
                        "id" => "schedule",
                        "title" => $opt . BookingTranslator::Create_item("schedule", $lang),
                        "url" => "bookingscheduling/" . $id_space,
                        "done" => $modelSchedule->getForSpace($id_space)
                    ],
                    [
                        "id" => "auth",
                        "title" => $opt . BookingTranslator::Create_item("authorisations", $lang),
                        "url" => "corespaceuseredit/" . $id_space,
                        "done" => $modelBkAuth->getForSpace($id_space),
                        "options" => [
                            "list" => $modelUser->getSpaceActiveUsers($id_space),
                            "defaultText" => UsersTranslator::User_account($lang)
                        ]
                    ],
                    [
                        "id" => "access",
                        "title" => BookingTranslator::Create_item("access", $lang),
                        "url" => "bookingaccessibilities/" . $id_space,
                        "done" => $modelBkAccess->getAll($id_space)
                    ],
                    [
                        "id" => "booking",
                        "title" => BookingTranslator::Create_item("booking", $lang),
                        "url" => "bookingdayarea/" . $id_space,
                        "done" => $modelBkEntry->countForSpace($id_space)
                    ]
                ]
            ];
    }

    protected function preconfigureSpace($space) {
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

    protected function activateModule($moduleName, $status, $space) {
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
    
    public function deleteAction($id_space){
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
        $model = new CoreSpace();
        $model->delete($id_space);
        $this->redirect("spaceadmin");
        
    }

}
