<?php

require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/booking/Model/BkAuthorization.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/core/Model/CoreTranslator.php';

require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';

/**
 * Class defining methods for statistics calculation for users
 *
 * @author Sylvain Prigent
 */
class BkStatsUser extends Model
{
    /**
     * Statistics of the users allowed to book a resource
     * @param number $resource_id
     */
    public function authorizedUsers($file, $resource_id, $id_space, $lang='en', $mail=false)
    {
        // get resource category
        $modelResource = new ReCategory();
        $resourceInfo = $modelResource->getName($id_space, $resource_id);
        if (!$resourceInfo) {
            throw new PfmParamException('resource not found');
        }
        // header
        $today = date('d/m/Y');
        $equipement = $resourceInfo;
        $header = "Date d'édition de ce document : \n" . $today;
        $titre = BookingTranslator::Authorisations_for($lang)." : " . $equipement;
        $avertissement = BookingTranslator::resourceRequiresAuthorization($lang);
        $reservation = BookingTranslator::resourceReservation($lang)." " . Configuration::get("name");

        // file name
        $id = $resource_id;
        $nom = date('Y-m-d-H-i') . "_" . $id . ".xlsx";
        $teamName = Configuration::get("name");
        $footer = "platform-manager/" . $teamName . "/exportFiles/" . $nom;

        $modelAuthorisation = new BkAuthorization();
        $res = $modelAuthorisation->getActiveAuthorizationSummaryForResourceCategory($id_space, $resource_id, $lang);

        // Création de l'objet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Définition de quelques propriétés
        $spreadsheet->getProperties()->setCreator($teamName);
        $spreadsheet->getProperties()->setLastModifiedBy($teamName);
        $spreadsheet->getProperties()->setTitle(BookingTranslator::Authorisations_statistics($lang));
        $spreadsheet->getProperties()->setSubject(ResourcesTranslator::resource($lang)." = " . $equipement);
        $spreadsheet->getProperties()->setDescription("");

        $center = array('alignment' => array('horizontal' =>  PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' =>  PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER));
        $gras = array('font' => array('bold' => true));
        $border = array(
            'borders' => array(
                'top' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN),
                'left' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN),
                'right' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN),
                'bottom' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));
        $borderLR = array(
            'borders' => array(
                'top' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE),
                'left' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN),
                'right' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN),
                'bottom' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE)));

        $borderLRB = array(
            'borders' => array(
                'top' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE),
                'left' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN),
                'right' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN),
                'bottom' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));

        $style = array(
            'font' => array(
                'bold' => false,
                'color' => array('rgb' => '000000'),
                'size' => 15,
                'name' => 'Calibri'
        ));

        $style2 = array(
            'font' => array(
                'bold' => false,
                'color' => array('rgb' => '000000'),
                'size' => 10,
                'name' => 'Calibri'
        ));

        // Nommage de la feuille
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(BookingTranslator::Authorized_users($lang));

        // Mise en page de la feuille
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->setBreak('A55', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
        $sheet->setBreak('E55', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_COLUMN);
        $sheet->setBreak('A110', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
        $sheet->setBreak('E110', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_COLUMN);
        $sheet->setBreak('A165', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
        $sheet->setBreak('E165', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_COLUMN);
        $sheet->setBreak('A220', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
        $sheet->setBreak('E220', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_COLUMN);
        $sheet->getPageMargins()->SetTop(0.9);
        $sheet->getPageMargins()->SetBottom(0.5);
        $sheet->getPageMargins()->SetLeft(0.2);
        $sheet->getPageMargins()->SetRight(0.2);
        $sheet->getPageMargins()->SetHeader(0.2);
        $sheet->getPageMargins()->SetFooter(0.2);
        $sheet->getPageSetup()->setHorizontalCentered(true);

        $sheet->getColumnDimension('A')->setWidth(32);
        $sheet->getColumnDimension('B')->setWidth(30);
        if ($mail) {
            $sheet->getColumnDimension('C')->setWidth(40);
        } else {
            $sheet->getColumnDimension('C')->setWidth(16);
            $sheet->getColumnDimension('D')->setWidth(8);
        }



        $sqlIcon = "SELECT image FROM core_spaces WHERE id=?";
        $reqIcon = $this->runRequest($sqlIcon, array($id_space))->fetch();

        if ($reqIcon && $reqIcon['image']) {
            // Header
            $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooterDrawing();
            $objDrawing->setName('space logo');
            $objDrawing->setPath($reqIcon[0]);
            $objDrawing->setHeight(60);
            $spreadsheet->getActiveSheet()->getHeaderFooter()->addImage($objDrawing, \PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooter::IMAGE_HEADER_LEFT);
        }
        $sheet->getHeaderFooter()->setOddHeader('&L&G&R' . $header);


        // Titre
        $ligne = 1;
        $colonne = 'A';
        $sheet->getRowDimension($ligne)->setRowHeight(30);
        $sheet->SetCellValue($colonne . $ligne, $titre);
        $sheet->getStyle($colonne . $ligne)->applyFromArray($style);
        $sheet->getStyle($colonne . $ligne)->applyFromArray($gras);
        $sheet->getStyle($colonne . $ligne)->applyFromArray($center);
        $sheet->getStyle($colonne . $ligne)->getAlignment()->setWrapText(true);
        $sheet->mergeCells($colonne . $ligne . ':D' . $ligne);

        // Avertissement
        $ligne = 2;
        $sheet->getRowDimension($ligne)->setRowHeight(20);
        $sheet->mergeCells('A' . $ligne . ':D' . $ligne);
        $sheet->SetCellValue('A' . $ligne, $avertissement);
        $sheet->getStyle('A' . $ligne)->applyFromArray($gras);
        $sheet->getStyle('A' . $ligne)->applyFromArray($center);
        $sheet->getStyle('A' . $ligne)->getAlignment()->setWrapText(true);

        // Réservation
        $ligne = 3;
        $sheet->getRowDimension($ligne)->setRowHeight(20);
        $sheet->mergeCells('A' . $ligne . ':D' . $ligne);
        $sheet->SetCellValue('A' . $ligne, $reservation);
        $sheet->getStyle('A' . $ligne)->applyFromArray($center);


        $ligne = 5;
        $sheet->SetCellValue('A' . $ligne, CoreTranslator::Name($lang));
        $sheet->getStyle('A' . $ligne)->applyFromArray($border);
        $sheet->getStyle('A' . $ligne)->applyFromArray($center);
        $sheet->getStyle('A' . $ligne)->applyFromArray($gras);
        $sheet->SetCellValue('B' . $ligne, ClientsTranslator::Client($lang));
        $sheet->getStyle('B' . $ligne)->applyFromArray($border);
        $sheet->getStyle('B' . $ligne)->applyFromArray($center);
        $sheet->getStyle('B' . $ligne)->applyFromArray($gras);
        if ($mail) {
            $sheet->SetCellValue('C' . $ligne, CoreTranslator::Email($lang));
            $sheet->getStyle('C' . $ligne)->applyFromArray($border);
            $sheet->getStyle('C' . $ligne)->applyFromArray($center);
            $sheet->getStyle('C' . $ligne)->applyFromArray($gras);
        } else {
            $sheet->SetCellValue('C' . $ligne, CoreTranslator::Date($lang));
            $sheet->getStyle('C' . $ligne)->applyFromArray($border);
            $sheet->getStyle('C' . $ligne)->applyFromArray($center);
            $sheet->getStyle('C' . $ligne)->applyFromArray($gras);
            $sheet->SetCellValue('D' . $ligne, BookingTranslator::Visa($lang));
            $sheet->getStyle('D' . $ligne)->applyFromArray($border);
            $sheet->getStyle('D' . $ligne)->applyFromArray($center);
            $sheet->getStyle('D' . $ligne)->applyFromArray($gras);
        }


        $ligne = 6;
        foreach ($res as $r) {
            $colonne = 'A';
            $sheet->getRowDimension($ligne)->setRowHeight(13);

            $sheet->SetCellValue($colonne . $ligne, $r["name"] . " " . $r["firstname"]); // user name
            $sheet->getStyle($colonne . $ligne)->applyFromArray($style2);
            $sheet->getStyle($colonne . $ligne)->applyFromArray($center);
            $sheet->getStyle($colonne . $ligne)->applyFromArray($borderLR);
            $colonne++;
            $sheet->SetCellValue($colonne . $ligne, $r["unitName"]); // unit name
            $sheet->getStyle($colonne . $ligne)->applyFromArray($style2);
            $sheet->getStyle($colonne . $ligne)->applyFromArray($center);
            $sheet->getStyle($colonne . $ligne)->applyFromArray($borderLR);
            $colonne++;
            
            if ($mail) {
                $sheet->SetCellValue($colonne . $ligne, CoreTranslator::dateFromEn($r["email"], $lang));
                $sheet->getStyle($colonne . $ligne)->applyFromArray($style2);
                $sheet->getStyle($colonne . $ligne)->applyFromArray($center);
                $sheet->getStyle($colonne . $ligne)->applyFromArray($borderLR);
            } else {
                $sheet->SetCellValue($colonne . $ligne, CoreTranslator::dateFromEn($r["date"], $lang));
                $sheet->getStyle($colonne . $ligne)->applyFromArray($style2);
                $sheet->getStyle($colonne . $ligne)->applyFromArray($center);
                $sheet->getStyle($colonne . $ligne)->applyFromArray($borderLR);
                $colonne++;
                $sheet->SetCellValue($colonne . $ligne, $r["visa"]); // visa name
                $sheet->getStyle($colonne . $ligne)->applyFromArray($style2);
                $sheet->getStyle($colonne . $ligne)->applyFromArray($center);
                $sheet->getStyle($colonne . $ligne)->applyFromArray($borderLR);
            }

            if (!($ligne % 55)) {
                $sheet->getStyle('A' . $ligne)->applyFromArray($borderLRB);
                $sheet->getStyle('B' . $ligne)->applyFromArray($borderLRB);
                $sheet->getStyle('C' . $ligne)->applyFromArray($borderLRB);
                $sheet->getStyle('D' . $ligne)->applyFromArray($borderLRB);
                $ligne++;
                // Titre
                $colonne = 'A';
                $sheet->getRowDimension($ligne)->setRowHeight(30);
                $sheet->SetCellValue($colonne . $ligne, $titre);
                $sheet->getStyle($colonne . $ligne)->applyFromArray($style);
                $sheet->getStyle($colonne . $ligne)->applyFromArray($gras);
                $sheet->getStyle($colonne . $ligne)->applyFromArray($center);
                $sheet->getStyle($colonne . $ligne)->getAlignment()->setWrapText(true);
                $sheet->mergeCells($colonne . $ligne . ':D' . $ligne);

                // Avertissement
                $ligne++;
                $sheet->mergeCells('A' . $ligne . ':D' . $ligne);
                $sheet->SetCellValue('A' . $ligne, $avertissement);
                $sheet->getStyle('A' . $ligne)->applyFromArray($gras);
                $sheet->getStyle('A' . $ligne)->applyFromArray($center);
                $sheet->getStyle('A' . $ligne)->getAlignment()->setWrapText(true);

                // Réservation
                $ligne++;
                $sheet->mergeCells('A' . $ligne . ':D' . $ligne);
                $sheet->SetCellValue('A' . $ligne, $reservation);
                $sheet->getStyle('A' . $ligne)->applyFromArray($center);

                // Noms des colonnes
                $ligne+=2;
                $sheet->SetCellValue('A' . $ligne, CoreTranslator::Name($lang));
                $sheet->getStyle('A' . $ligne)->applyFromArray($border);
                $sheet->getStyle('A' . $ligne)->applyFromArray($center);
                $sheet->getStyle('A' . $ligne)->applyFromArray($gras);
                $sheet->SetCellValue('B' . $ligne, ClientsTranslator::Client($lang));
                $sheet->getStyle('B' . $ligne)->applyFromArray($border);
                $sheet->getStyle('B' . $ligne)->applyFromArray($center);
                $sheet->getStyle('B' . $ligne)->applyFromArray($gras);
                if ($mail) {
                    $sheet->SetCellValue('C' . $ligne, CoreTranslator::Email($lang));
                    $sheet->getStyle('C' . $ligne)->applyFromArray($border);
                    $sheet->getStyle('C' . $ligne)->applyFromArray($center);
                    $sheet->getStyle('C' . $ligne)->applyFromArray($gras);
                } else {
                    $sheet->SetCellValue('C' . $ligne, CoreTranslator::Date($lang));
                    $sheet->getStyle('C' . $ligne)->applyFromArray($border);
                    $sheet->getStyle('C' . $ligne)->applyFromArray($center);
                    $sheet->getStyle('C' . $ligne)->applyFromArray($gras);
                    $sheet->SetCellValue('D' . $ligne, BookingTranslator::Visa($lang));
                    $sheet->getStyle('D' . $ligne)->applyFromArray($border);
                    $sheet->getStyle('D' . $ligne)->applyFromArray($center);
                    $sheet->getStyle('D' . $ligne)->applyFromArray($gras);
                }
            }
            $ligne++;
        }
        $ligne--;
        $sheet->getStyle('A' . $ligne)->applyFromArray($borderLRB);
        $sheet->getStyle('B' . $ligne)->applyFromArray($borderLRB);
        $sheet->getStyle('C' . $ligne)->applyFromArray($borderLRB);
        $sheet->getStyle('D' . $ligne)->applyFromArray($borderLRB);

        // Footer
        $sheet->getHeaderFooter()->setOddFooter('&L ' . $footer . '&R Page &P / &N');
        $sheet->getHeaderFooter()->setEvenFooter('&L ' . $footer . '&R Page &P / &N');

        if ($reqIcon && $reqIcon['image']) {
            $ImageNews = $reqIcon[0];

            //on récupère l'extension du fichier
            $ExtensionPresumee = explode('.', $ImageNews);
            $ExtensionPresumee = strtolower($ExtensionPresumee[count($ExtensionPresumee) - 1]);
            //on utilise la fonction php associé au bon type d'image
            if ($ExtensionPresumee == 'jpg' || $ExtensionPresumee == 'jpeg') {
                $ImageChoisie = imagecreatefromjpeg($ImageNews);
            } elseif ($ExtensionPresumee == 'gif') {
                $ImageChoisie = imagecreatefromgif($ImageNews);
            } elseif ($ExtensionPresumee == 'png') {
                $ImageChoisie = imagecreatefrompng($ImageNews);
            }

            //je redimensionne l’image
            $TailleImageChoisie = getimagesize($ImageNews);
            //la largeur voulu dans le document excel
            //$NouvelleLargeur = 150;
            $NouvelleHauteur = 80;
            //calcul du pourcentage de réduction par rapport à l’original
            $Reduction = (($NouvelleHauteur * 100) / $TailleImageChoisie[1]);
            //PHPExcel m’aplatit verticalement l’image donc j’ai calculé de ratio d’applatissement de l’image et je l’étend préalablement
            $NouvelleLargeur = (($TailleImageChoisie[0] * $Reduction) / 100);
            //j’initialise la nouvelle image
            $NouvelleImage = imagecreatetruecolor($NouvelleLargeur, $NouvelleHauteur);

            //je mets l’image obtenue après redimensionnement en variable
            imagecopyresampled($NouvelleImage, $ImageChoisie, 0, 0, 0, 0, $NouvelleLargeur, $NouvelleHauteur, $TailleImageChoisie[0], $TailleImageChoisie[1]);
            $gdImage = $NouvelleImage;

            //on créé l’objet de dessin et on lui donne un nom, l’image, la position de l’image, la compression de l’image, le type mime…
            $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing();
            $objDrawing->setName('space image');
            $objDrawing->setImageResource($gdImage);
            $objDrawing->setCoordinates('A1');
            $objDrawing->setOffsetX(50);
            $objDrawing->setOffsetY(8);
            $objDrawing->setRenderingFunction(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::RENDERING_JPEG);
            $objDrawing->setMimeType(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_DEFAULT);
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer->save($file);
    }

    public function bookingUsers($id_space, $startdate, $enddate)
    {
        // convert start date to unix date
        if ($startdate == "") {
            throw new PfmParamException("invalid start date");
        }
        if ($enddate == "") {
            throw new PfmParamException("invalid end date");
        }
        $tabDate = explode("-", $startdate);
        $searchDate_start = mktime(0, 0, 0, intval($tabDate[1]), intval($tabDate[2]), intval($tabDate[0]));

        // convert end date to unix date
        $tabDate = explode("-", $enddate);
        $searchDate_end = mktime(0, 0, 0, intval($tabDate[1]), intval($tabDate[2]) + 1, intval($tabDate[0]));

        //  get all the booking users
        $q = array('start' => $searchDate_start, 'end' => $searchDate_end, 'space' => $id_space);
        $sql = 'SELECT DISTINCT recipient_id FROM bk_calendar_entry WHERE
                (start_time >=:start AND start_time <= :end)
                AND deleted=0 AND id_space=:space';
        $req = $this->runRequest($sql, $q);
        $recs = $req->fetchAll();

        $sql2 = 'SELECT DISTINCT responsible_id FROM bk_calendar_entry WHERE
                (start_time >=:start AND start_time <= :end)
                AND deleted=0 AND id_space=:space';
        $req2 = $this->runRequest($sql2, $q);
        $recresps = $req2->fetchAll();

        // get the users informations (name, firstname, unit, email)
        $modelUser = new CoreUser();
        $modelClient = new ClClient();
        $recss = array();
        for ($i = 0; $i < count($recs); $i++) {
            $recss[] = array('name' => $modelUser->getUserFUllName($recs[$i]['recipient_id']),
                'email' => $modelUser->getEmail($recs[$i]['recipient_id']));
        }
        for ($i = 0; $i < count($recresps); $i++) {
            $clientInfo = $modelClient->get($id_space, $recresps[$i]['responsible_id']);
            $recss[] = array(
                'name' => $clientInfo["contact_name"],
                'email' => $clientInfo["email"]
            );
        }

        return $recss;
    }
}
