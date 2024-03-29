<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/core/Model/CoreInstall.php';

/**
 * @deprecated
 * @author sprigent
 *     Install the Core database
 */
class CoreinstallController extends Controller
{
    protected $installModel;
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->installModel = new CoreInstall();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction($message = "")
    {
        $lang = $this->getLanguage();

        $form = new Form($this->request, "installform");
        $form->setTitle(CoreTranslator::SQL_configuration($lang));
        $form->setSubTitle(CoreTranslator::this_will_edit_the_configuration_file($lang));
        $form->addText("sql_host", coreTranslator::sql_host($lang), true, "localhost");
        $form->addText("login", coreTranslator::login($lang), true, "root");
        $form->addPassword("password", coreTranslator::password($lang), false);
        $form->addText("db_name", coreTranslator::db_name($lang), true, "");
        $form->setValidationButton(coreTranslator::Next($lang), "install");


        if ($form->check()) {
            // get request variables
            $sql_host = $this->request->getParameter("sql_host");
            $login = $this->request->getParameter("login");
            $password = $this->request->getParameter("password");
            $db_name = $this->request->getParameter("db_name");

            // test the connection
            $installModel = new CoreInstall();
            $testVal = $installModel->testConnection($sql_host, $login, $password, $db_name);
            //echo 'test connection return val = ' . $testVal . '-----';
            if ($testVal == 'success') {
                // edit the config file
                $this->writedbConfig($sql_host, $login, $password, $db_name);

                $modelCreateDatabase = new CoreInstall();
                $dsn = 'mysql:host=' . $sql_host . ';dbname=' . $db_name . ';charset=utf8';
                $modelCreateDatabase->setDatabase($dsn, $login, $password);
                $modelCreateDatabase->createDatabase();

                $this->redirect("coretiles");
            } else {
                $this->indexAction($testVal);
            }
        }
        $formHtml = $form->getHtml($lang);
        return $this->render(array("formHtml" => $formHtml, "message" => $message));
    }

    /**
     *
     * @param type $sql_host
     * @param type $login
     * @param type $password
     * @param type $db_name
     * @throws Exception
     */
    private function writedbConfig($sql_host, $login, $password, $db_name)
    {
        if (!$this->installModel->writedbConfig($sql_host, $login, $password, $db_name)) {
            throw new PfmException("Cannot write db config file", 500);
        }
    }
}
