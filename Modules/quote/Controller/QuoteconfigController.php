<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/quote/Model/QuoteInstall.php';
require_once 'Modules/quote/Model/QuoteTranslator.php';
require_once 'Modules/quote/Controller/QuotelistController.php';

require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class QuoteconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        
        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        // maintenance form
        $formMenusactivation = $this->menusactivationForm($id_space, 'quote', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'quote', 'book');
            return $this->redirect("quoteconfig/".$id_space);
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang));
        
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    public function pdftemplateAction($id_space) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $currentTemplate = 'data/quote/' . $id_space . '/template.twig';
        if (! file_exists($currentTemplate)) {

            // backwark, quotes were using invoices templates, can still use it as default
            $currentTemplate = 'data/quote/' . $id_space . '/template.php';
            if (! file_exists($currentTemplate)) {
                $currentTemplate = null;
            }
            
            if(!$currentTemplate) {
                $_SESSION['flash'] = 'Using default template';
                $_SESSION['flashClass'] = 'warning';
            }
        }

        $formDownload = new Form($this->request, "formDownloadTemplate");
        $formDownload->setTitle(QuoteTranslator::currentTemplate($lang));
        $hasTemplate = false;
        $template = 'data/quote/' . $id_space . '/template.twig';
        if(file_exists('data/quote/' . $id_space . '/template.twig')) {
            $hasTemplate = true;
        } else if (file_exists('data/quote/' . $id_space . '/template.php')) {
            $hasTemplate = true;
            $template = 'data/quote/' . $id_space . '/template.php';
        } else {
            $template = 'externals/pfm/templates/quote_template.twig';
        }
        $templateName = basename($template);

        $formDownload->addDownloadButton("url", QuoteTranslator::Download($lang), $templateName);
        if ($formDownload->check()) {
            if(!file_exists($template)) {
                throw new PfmFileException('File not found', 404);
            }
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$templateName");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");

            // read the file from disk
            readfile($template);
            return;
        }

        $formPreview = new Form($this->request, "formPreviewTemplate");
        $formPreview->setTitle('Preview');
        if($hasTemplate) {
            $formPreview->addDownloadButton("url", "Preview", "template");
        }
        if($formPreview->check()) {
            Configuration::getLogger()->debug('[quote][template] preview');
            if(!$hasTemplate) {
               throw new PfmParamException('no template available');
            }
            $f = $this->checkTemplate($id_space);
            Configuration::getLogger()->debug('[quote][template] should show', ['f' => $f]);
            $mime = mime_content_type($f);
            header('Content-Description: File Transfer');
            header('Content-Type: '.$mime);
            header('Content-Disposition: attachment; filename="quote.pdf"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($f));
            readfile($f);
            return;
        }   

        $formUpload = new Form($this->request, "formUploadTemplate");
        $formUpload->setTitle(QuoteTranslator::uploadTemplate($lang));
        $formUpload->addUpload("template", "");
        $formUpload->setValidationButton(CoreTranslator::Ok($lang), "/quote/$id_space/pdftemplate");
        $formUpload->setColumnsWidth(0, 12);
        $formUpload->setButtonsWidth(2, 10);
        if ($formUpload->check()) {
            if (!file_exists('data/quote/' . $id_space)) {
                mkdir('data/quote/' . $id_space, 0777, true);
            }

            if(file_exists('data/quote/' . $id_space . '/template.twig')) {
                // backup
                Configuration::getLogger()->debug('[quote][template] backup existing template');
                copy('data/quote/' . $id_space . '/template.twig', 'data/quote/' . $id_space . '/template.twig.save');
            }
            FileUpload::uploadFile('data/quote/' . $id_space . '/', 'template', 'template.twig');

            try {
                $this->checkTemplate($id_space);
            } catch(Exception $e) {
                if(file_exists('data/quote/' . $id_space . '/template.twig.save')) {
                    // backup
                    Configuration::getLogger()->debug('[quote][template] revert existing template');
                    copy('data/quote/' . $id_space . '/template.twig.save', 'data/quote/' . $id_space . '/template.twig');
                    unlink('data/quote/' . $id_space . '/template.twig.save');
                }
                Configuration::getLogger()->debug('[quote][template] invalid template', ['error' => $e->getMessage()]);
                throw $e;
            }

            if(file_exists('data/quote/' . $id_space . '/template.twig.save')) {
                unlink('data/quote/' . $id_space . '/template.twig.save');
            }

            $_SESSION['flash'] = QuoteTranslator::TheTemplateHasBeenUploaded($lang);
            $_SESSION["flashClass"] = 'success';
            $this->redirect("quote/$id_space/pdftemplate");
            return;
        }

        $formUploadImages = new Form($this->request, "formUploadImages");
        $formUploadImages->setTitle(QuoteTranslator::UploadImages($lang));
        $formUploadImages->addUpload("image", "");
        $formUploadImages->setValidationButton(CoreTranslator::Ok($lang), "/quote/$id_space/pdftemplate");
        $formUploadImages->setButtonsWidth(2, 10);
        $formUploadImages->setColumnsWidth(0, 12);
        if ($formUploadImages->check()) {
            if (!file_exists('data/quote/' . $id_space)) {
                mkdir('data/quote/' . $id_space, 0777, true);
            }
            FileUpload::uploadFile('data/quote/' . $id_space . '/', 'image', $_FILES["image"]["name"]);
            $this->redirect("quote/$id_space/pdftemplate");
            return;
        }

        $dataTable = new TableView();
        $dataTable->setTitle(QuoteTranslator::Images($lang));
        $dataTable->addDeleteButton("quote/$id_space/pdftemplatedelete");

        $data = array();

        if (file_exists('data/quote/' . $id_space)) {
            $files = scandir('data/quote/' . $id_space);

            foreach ($files as $file) {
                if (strpos($file, ".") > 0 && $file != "template.twig" && !str_ends_with($file, '.pdf')) {
                    $data[] = array('name' => $file, 'id' => str_replace('.', "__pm__", $file));
                }
            }
        }

        $headers = array(
            "name" => QuoteTranslator::Name($lang)
        );

        $tableHtml = $dataTable->view($data, $headers);

        $this->render(array("id_space" => $id_space,
            "formDownload" => $currentTemplate ? $formDownload->getHtml($lang): '',
            "formPreview" => $formPreview->getHtml($lang),
            "formUpload" => $formUpload->getHtml($lang), "tableHtml" => $tableHtml,
            "formUploadImages" => $formUploadImages->getHtml($lang),
            "lang" => $lang));
    }

    public function pdftemplatedeleteAction($id_space, $name) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $namefile = str_replace("__pm__", '.', $name);
        unlink('data/quote/' . intval($id_space) . '/' . $namefile);
        $this->redirect("quote/$id_space/pdftemplate");
    }

    function checkTemplate($id_space) {
        $lang = $this->getLanguage();
        $client = (new ClClient())->get($id_space, 0);
        $client["name"] = "my client";
        $client["address_delivery"] = "in britany of course";
        $client["address_invoice"] = "somewhere in France";
        $client["phone"] = "112";
        $client["email"] = "do-not-reply@pfm.org";

        $date = date('Y-m-d');
        $address = "Somewhere\nover the\nrainbow";
        $total = 100;
        $resp = "A pfm user";
        $unit = "My prefered customer";
        $number = 'fake';
        $table = '<table class="table"><thead><tr><th>quote details</th></tr></thead><tbody><tr><td>some details</td></tr></tbody></table>';

        $dest = null;
        try {
            $c = new QuotelistController($this->request, $this->currentSpace);
            $dest = $c->generatePDF($id_space, [
                'id' => 0,
                'number' => $number,
                'date' => $date,
                'unit' => $unit,
                'resp' => $resp,
                'address' => $address,
                'table' => $table,
                'total' => $total,
                'useTTC' => true,
                'details' => '',
                'clientInfos' => $client,
                'quoteInfos' => ['title' => ''],
            ], $lang, true);
        } catch(Exception $e) {
            throw new PfmParamException('Invalid template: '.$e->getMessage());
        }
        return $dest;
    }

}
