<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/com/Model/ComInstall.php';
require_once 'Modules/com/Model/ComTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class ComconfigController extends CoresecureController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }


    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space)
    {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($id_space, 'com', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'com', 'info-circle');
            return $this->redirect("comconfig/" . $id_space);
        }

        $useComAsSpaceHomePageForm = $this->useComAsSpaceHomePage($lang, $id_space);
        if ($useComAsSpaceHomePageForm->check()) {
            $modelConfig = new CoreConfig();

            $use_space_home_page = $this->request->getParameter('use_space_home_page');
            if ($use_space_home_page == 1) {
                $modelConfig->setParam('space_home_page', 'comhome', $id_space);
            } else {
                $modelConfig->setParam('space_home_page', '', $id_space);
            }

            return $this->redirect("comconfig/" . $id_space);
        }

        $twitterForm = $this->twitterConfigForm($lang, $id_space);
        if ($twitterForm->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("use_twitter", $this->request->getParameter("use_twitter"), $id_space);
            $modelConfig->setParam("twitter_oauth_access_token", $this->request->getParameter("twitter_oauth_access_token"), $id_space);
            $modelConfig->setParam("twitter_oauth_access_token_sec", $this->request->getParameter("twitter_oauth_access_token_secret"), $id_space);
            $modelConfig->setParam("twitter_consumer_key", $this->request->getParameter("twitter_consumer_key"), $id_space);
            $modelConfig->setParam("twitter_consumer_secret", $this->request->getParameter("twitter_consumer_secret"), $id_space);

            return $this->redirect("comconfig/" . $id_space);
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
            $useComAsSpaceHomePageForm->getHtml($lang),
            $twitterForm->getHtml($lang));

        return $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function useComAsSpaceHomePage($lang, $id_space)
    {
        $modelConfig = new CoreConfig();
        $space_home_page = $modelConfig->getParamSpace("space_home_page", $id_space);
        $useSpaceHomePage = 0;
        if ($space_home_page == "comhome") {
            $useSpaceHomePage = 1;
        }

        $form = new Form($this->request, "useComAsSpaceHomePageForm");
        $form->addSeparator(ComTranslator::useComAsSpaceHomePage($lang));

        $form->addSelect("use_space_home_page", "", array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $useSpaceHomePage);

        $form->setValidationButton(CoreTranslator::Save($lang), "comconfig/" . $id_space);


        return $form;
    }

    protected function twitterConfigForm($lang, $id_space)
    {
        $modelConfig = new CoreConfig();
        $use_twitter = $modelConfig->getParamSpace("use_twitter", $id_space);
        $twitter_oauth_access_token = $modelConfig->getParamSpace("twitter_oauth_access_token", $id_space);
        $twitter_oauth_access_token_secret = $modelConfig->getParamSpace("twitter_oauth_access_token_sec", $id_space);
        $twitter_consumer_key = $modelConfig->getParamSpace("twitter_consumer_key", $id_space);
        $twitter_consumer_secret = $modelConfig->getParamSpace("twitter_consumer_secret", $id_space);

        $form = new Form($this->request, "twitterConfigForm");
        $form->setTitle(ComTranslator::Twitter($lang));
        $form->addSelect("use_twitter", ComTranslator::UserTwitter($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $use_twitter);

        $form->addText("twitter_oauth_access_token", ComTranslator::AuthAccessToken($lang), false, $twitter_oauth_access_token);
        $form->addText("twitter_oauth_access_token_secret", ComTranslator::AuthAccessTokenSecret($lang), false, $twitter_oauth_access_token_secret);
        $form->addText("twitter_consumer_key", ComTranslator::ConsumerKey($lang), false, $twitter_consumer_key);
        $form->addText("twitter_consumer_secret", ComTranslator::ConsumerKeySecret($lang), false, $twitter_consumer_secret);

        $form->setValidationButton(CoreTranslator::Save($lang), "comconfig/" . $id_space);


        return $form;
    }
}
