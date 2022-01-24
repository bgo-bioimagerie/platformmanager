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
            $data[$i]['plan_expire'] = date('Y-m-d', $data[$i]['plan_expire']);
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
        if(!$space) {
            $space = CoreSpace::new();
        }

        $spaceAdmins = $modelSpace->spaceAdmins($id_space);
        
        $lang = $this->getLanguage();
        $form = new Form($this->request, "corespaceadminedit");
        $form->setTitle(CoreTranslator::Edit_space($lang));
        
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
            # $shortname = str_replace(" ", "", $shortname);
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
            if($isSuperAdmin) {
                return $this->redirect("spaceadmin", [], ['space' => $newSpace]);
            }
        }
        
        return $this->render(array("lang" => $lang, "formHtml" => $form->getHtml($lang), "data" => ["space" => $space]));
        
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
