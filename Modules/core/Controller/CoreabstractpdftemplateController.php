<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';
require_once 'Framework/Errors.php';


require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';

require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClCompany.php';


abstract class PfmTemplateController extends CoresecureController {

    public function generatePDF(int $id_space, string $module, mixed $data, object $translator, $toFile=false, $lang='en') {
        $address = nl2br($data['address']);
        $date = CoreTranslator::dateFromEn($data['date'], $lang);
        
        $csm = new CoreSpace();
        $space = $csm->getSpace($id_space);

        $clcm = new ClCompany();
        $company = $clcm->getForSpace($id_space);
        if(!isset($company['name'])) {
            $company = [
                'name' => $this->currentSpace['name'],
                'address' => '',
                'city' => 'Rennes',
                'zipcode' => '',
                'country' => '',
                'tel' => '',
                'email' => '',
                'approval_number' => ''
            ];
        }

        $number = $data['number'];
        
        if(!file_exists("data/$module/$id_space/template.twig") && file_exists("data/$module/$id_space/template.php")) {
            // backwark, templates were in PHP and no twig template available use old template
            ob_start();
            include("data/$module/$id_space/template.php");
            $content = ob_get_clean();
        } else {
            $template = "data/$module/$id_space/template.twig";
            if(!file_exists($template)){
                $template = 'externals/pfm/templates/'.$module.'_template.twig';
            }
            Configuration::getLogger()->debug('['.$module.'][pdf]', ['template' => $template]);

            $loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../../..');
            $twig = new \Twig\Environment($loader, []);
            $content = $twig->render($template, [
                'id' => '00000-0',
                'id_space' => $id_space,
                'number' => $data['number'],
                'date' => $date,
                'unit' => $data['unit'] ?? '',
                'resp' => $data['resp'],
                'address' => $address,
                'adress' => $address,  // backward compat
                'table' => $data['table'],
                'total' => $data['total'],
                'useTTC' => $data['useTTC'] ?? true,
                'details' => $data['details'] ?? '',
                'clientInfos' => $data['client'],
                'invoiceInfo' => $data['info'] ?? [],
                'translator' => $translator,
                'lang' => $lang,
                'company' => $company,
                'space' => $space,
                'isquote' => $data["isquote"] ?? false
            ]);
        }
        
        // convert in PDF
        $out = __DIR__."/../../../data/$module/$id_space/$module"."_"."$number.pdf";
        try {
            $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'fr');
            //$html2pdf->setModeDebug();
            $html2pdf->setDefaultFont('Arial');
            $html2pdf->writeHTML($content);
            if($toFile || getenv("PFM_MODE") == "test") {
                $html2pdf->Output($out, 'F');
            } else {
                $html2pdf->Output($unit . "_" . $resp . "_" . $number . '.pdf');
            }
        } catch (Exception $e) {
            throw new PfmException("Pdf generation error: " . $e, 500);
        }
        return $out;
    }


    function checkTemplate(int $id_space, string $module, object $translator) {
        $client = (new ClClient())->get($id_space, 0);
        $client["name"] = "my client";
        $client["address_delivery"] = "in britany of course";
        $client["address_invoice"] = "somewhere in France";
        $client["phone"] = "112";
        $client["email"] = "no-reply@pfm.org";

        $date = date('Y-m-d');
        $address = "Somewhere\nover the\nrainbow";
        $total = 100;
        $resp = "A pfm user";
        $unit = "My prefered customer";
        $number = 'fake';
        $table = '<table class="table"><thead><tr><th>some details</th></tr></thead><tbody><tr><td>some details</td></tr></tbody></table>';

        $lang = $this->getLanguage();

        $data = [
            'date' => $date,
            'address' => $address,
            'total' => $total,
            'resp' => $resp,
            'unit' => $unit,
            'number' => $number,
            'table' => $table,
            'client' => $client,
            'info' => ['title' => 'preview']
        ];

        $dest = null;
        try {
            $dest = $this->generatePDF($id_space, $module, $data, $translator, true, $lang);
        } catch(Exception $e) {
            throw new PfmParamException('Invalid template: '.$e->getMessage());
        }
        return $dest;
    }

    public function pdftemplate(int $id_space, string $module, object $translator) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $currentTemplate = 'data/'.$module.'/' . $id_space . '/template.twig';
        if (! file_exists($currentTemplate)) {
            $currentTemplate = 'data/'.$module.'/' . $id_space . '/template.php';
            if (! file_exists($currentTemplate)) {
                $currentTemplate = null;
            }

            if(!$currentTemplate) {
                $_SESSION['flash'] = 'Using default template';
                $_SESSION['flashClass'] = 'warning';
            }
        }

        $formDownload = new Form($this->request, "formDownloadTemplate");
        $formDownload->setTitle(CoreTranslator::currentTemplate($lang));
        $hasTemplate = false;
        $template = 'data/'.$module.'/' . $id_space . '/template.twig';
        if(file_exists('data/'.$module.'/' . $id_space . '/template.twig')) {
            $hasTemplate = true;
        } else if (file_exists('data/'.$module.'/' . $id_space . '/template.php')) {
            $hasTemplate = true;
            $template = 'data/'.$module.'/' . $id_space . '/template.php';
        } else {
            $template = 'externals/pfm/templates/'.$module.'_template.twig';
        }
        $templateName = basename($template);
        $formDownload->addDownloadButton("url", CoreTranslator::Download($lang), $templateName);

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
            return null;
        }

        $formPreview = new Form($this->request, "formPreviewTemplate");
        $formPreview->setTitle('Preview');
        if($hasTemplate) {
            $formPreview->addDownloadButton("url", "Preview", "template");
        }
        if($formPreview->check()) {
            Configuration::getLogger()->debug('[invoice][template] preview');
            if(!file_exists('data/'.$module.'/' . $id_space . '/template.twig') && !file_exists('data/invoices/' . $id_space . '/template.php')) {
               throw new PfmParamException('no template available');
            }
            $f = $this->checkTemplate($id_space, $module, $translator);
            Configuration::getLogger()->debug('['.$module.'][template] shoud show', ['f' => $f]);
            $mime = mime_content_type($f);
            header('Content-Description: File Transfer');
            header('Content-Type: '.$mime);
            header('Content-Disposition: attachment; filename="'.$module.'.pdf"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($f));
            readfile($f);
            return null;
        }   

        $formUpload = new Form($this->request, "formUploadTemplate");
        $formUpload->setTitle(CoreTranslator::uploadTemplate($lang));
        $formUpload->addUpload("template", "");
        $formUpload->setValidationButton(CoreTranslator::Ok($lang), "/$module/$id_space/pdftemplate");
        $formUpload->setColumnsWidth(0, 12);
        if ($formUpload->check()) {
            if (!file_exists('data/'.$module.'/' . $id_space)) {
                mkdir('data/'.$module.'/' . $id_space, 0755, true);
            }

            if(file_exists('data/'.$module.'/' . $id_space . '/template.twig')) {
                // backup
                Configuration::getLogger()->debug('['.$module.'][template] backup existing template');
                copy("data/$module/$id_space/template.twig", "data/$module/$id_space/template.twig.save");
            }
            FileUpload::uploadFile('data/'.$module.'/' . $id_space . '/', 'template', 'template.twig');

            try {
                $this->checkTemplate($id_space, $module, $translator);
            } catch(Exception $e) {
                if(file_exists('data/'.$module.'/' . $id_space . '/template.twig.save')) {
                    // backup
                    Configuration::getLogger()->debug('['.$module.'][template] revert existing template');
                    copy("data/$module/$id_space/template.twig.save", "data/$module/$id_space/template.twig");
                    unlink("data/$module/$id_space/template.twig.save");
                }
                Configuration::getLogger()->debug('['.$module.'][template] invalid template', ['error' => $e->getMessage()]);
                throw $e;
            }

            if(file_exists('data/'.$module.'/' . $id_space . '/template.twig.save')) {
                unlink('data/'.$module.'/' . $id_space . '/template.twig.save');
            }

            $_SESSION['flash'] = CoreTranslator::TheTemplateHasBeenUploaded($lang);
            $_SESSION["flashClass"] = 'success';
            return $this->redirect("$module/$id_space/pdftemplate");
        }

        $formUploadImages = new Form($this->request, "formUploadImages");
        $formUploadImages->setTitle(CoreTranslator::UploadImages($lang));
        $formUploadImages->addUpload("image", "");
        $formUploadImages->setValidationButton(CoreTranslator::Ok($lang), "$module/$id_space/pdftemplate");
        $formUploadImages->setColumnsWidth(0, 12);
        if ($formUploadImages->check()) {
            if (!file_exists('data/'.$module.'/' . $id_space)) {
                mkdir('data/'.$module.'/' . $id_space, 0755, true);
            }
            FileUpload::uploadFile('data/'.$module.'/' . $id_space . '/', 'image', $_FILES["image"]["name"]);
            return $this->redirect("$module/$id_space/pdftemplate");
        }

        $dataTable = new TableView();
        $dataTable->setTitle(CoreTranslator::Images($lang));
        $dataTable->addDeleteButton("$module/$id_space/pdftemplatedelete");

        $data = array();

        if (file_exists('data/'.$module.'/' . $id_space)) {
            $files = scandir('data/'.$module.'/' . $id_space);

            foreach ($files as $file) {
                if (strpos($file, ".") > 0 && $file != "template.twig" && !str_ends_with($file, '.pdf')) {
                    $data[] = array('name' => $file, 'id' => str_replace('.', "__pm__", $file));
                }
            }
        }

        $headers = array(
            "name" => CoreTranslator::Name($lang)
        );

        $tableHtml = $dataTable->view($data, $headers);

        return $this->render(array("id_space" => $id_space,
            "formDownload" => $currentTemplate ? $formDownload->getHtml($lang): '',
            "formPreview" => $formPreview->getHtml($lang),
            "formUpload" => $formUpload->getHtml($lang), "tableHtml" => $tableHtml,
            "formUploadImages" => $formUploadImages->getHtml($lang),
            "lang" => $lang));
    }

    public function pdftemplatedelete(int $id_space, string $module, string $name) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $namefile = str_replace("__pm__", '.', $name);
        unlink('data/'.$module.'/' . intval($id_space) . '/' . $namefile);
        return $this->redirect("$module/$id_space/pdftemplate");
    }

}
