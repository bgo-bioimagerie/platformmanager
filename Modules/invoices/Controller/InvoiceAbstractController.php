<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/invoices/Controller/InvoicesController.php';
require_once 'Modules/clients/Model/ClCompany.php';
require_once 'Modules/core/Model/CoreSpace.php';

use Fp\Cast as Fc;
use Fp\Collection as Fp;
use Fp\Evidence as Fv;
use Fp\Functional\Option\Option;

/**
 *
 * @author sprigent
 * Controller for the home page
 */
abstract class InvoiceAbstractController extends InvoicesController
{
    /**
     * To display the form that allows to edit an order and export as pdf
     */
    public abstract function editAction($id_space, $id_invoice, $pdf);

    /**
     * To delete the invoice data in the content tables
     */
    public abstract function deleteAction($id_space, $id_invoice);

    protected final function generatePDFInvoice($id_space, $invoice, $id_item, $lang, $details = ""): void
    {
        ["table" => $table, "total" => $total] =
            $this->mkInvoiceData($id_space, $invoice, $id_item, $lang);

        $modelClient = new ClClient();
        $unit = "";
        $adress = $modelClient->getAddressInvoice($id_space, $invoice["id_responsible"]);
        $clientInfos = $modelClient->get($id_space, $invoice["id_responsible"]);
        $resp = $clientInfos["contact_name"];

        $this->generatePDF($id_space
            , $invoice["id"]
            , CoreTranslator::dateFromEn($invoice["date_generated"], $lang)
            , $unit
            , $resp
            , $adress
            , $table
            , $total
            , details: $details
            , clientInfos: $clientInfos, lang: $lang);
    }

    /**
     * @param int $id_space
     * @param array{discount: mixed} $invoice
     * @param int $id_item
     * @param string $lang
     * @return array{table: string, total: float}
     */
    protected final function mkInvoiceData(int $id_space, array $invoice, int $id_item, string $lang): array
    {
        $content = new InvoiceContent($this->mkInvoiceEntries($id_space, $id_item, $lang), $invoice["discount"]);

        return [ "table" => self::mkInvoiceTable($content, $lang), "total" => $content->total ];
    }

    /**
     * Gather the data to be rendered in PDF
     *
     * @param int $id_space
     * @param int $id_item
     * @param string $lang
     * @return array<InvoiceEntry>
     */
    protected function mkInvoiceEntries(int $id_space, int $id_item, string $lang): array {
        $modelItem = new InInvoiceItem();
        $modelService = new SeService();

        $contentArray = explode(";", $modelItem->getItem($id_space, $id_item)["content"]);

        $data = Fp\filterMap($contentArray
                           , fn($content) => substr_count($content, "=") > 2
                               ? Option::some(explode("=", $content))
                               : Option::none());

        $groupedLines = Fp\groupMap($data
                                  , fn($d) => $d[0]
                                  , self::mkInvoiceLine(...));

        return Fp\mapKV($groupedLines, fn($id, $lines) =>
            new InvoiceEntry($id, $modelService->getItemName($id_space, $id, true) ?? Constants::UNKNOWN, $lines));
    }

    private function generatePDF($id_space, $invoice_id, $date, $unit, $resp, $address, $table, $total, $details = "", $clientInfos = null, $lang='en'): void
    {
        $address = nl2br($address);
        $date = CoreTranslator::dateFromEn($date, $lang);

        $modelInvoice = new InInvoice();
        $invoiceInfo = $modelInvoice->get($id_space, $invoice_id);
        $number = $invoiceInfo['number'];
        $invoiceInfo['module'] = InvoicesTranslator::Module($invoiceInfo['module'], $lang);

        $translator = new InvoicesTranslator();
        $csm = new CoreSpace();
        $space = $csm->getSpace($id_space);

        $clcm = new ClCompany();
        $company = $clcm->getForSpace($id_space);
        if (!isset($company['name'])) {
            $company = [
                'name' => $space['name'],
                'address' => '',
                'city' => '',
                'zipcode' => '',
                'country' => '',
                'tel' => '',
                'email' => '',
                'approval_number' => ''
            ];
        }

        if (!file_exists('data/invoices/'.$id_space.'/template.twig') && file_exists('data/invoices/'.$id_space.'/template.php')) {
            // backwark, templates were in PHP and no twig template available use old template
            ob_start();
            include('data/invoices/'.$id_space.'/template.php');
            $content = ob_get_clean();
        } else {
            $template = 'data/invoices/'.$id_space.'/template.twig';
            if (!file_exists($template)) {
                $template = 'externals/pfm/templates/invoices_template.twig';
            }
            Configuration::getLogger()->debug('[invoices][pdf]', ['template' => $template]);

            $loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../../..');
            $twig = new \Twig\Environment($loader, []);
            $content = $twig->render($template, [
                'id_space' => $id_space,
                'number' => $number,
                'date' => $date,
                'unit' => $unit,
                'resp' => $resp,
                'address' => $address,
                'adress' => $address,  // backward compat
                'table' => $table,
                'total' => $total,
                'useTTC' => true,
                'details' => $details,
                'clientInfos' => $clientInfos,
                'invoiceInfo' => $invoiceInfo,
                'translator' => $translator,
                'lang' => $lang,
                'company' => $company,
                'space' => $space
            ]);
        }

        // convert in PDF
        $out = __DIR__."/../../../data/invoices/$id_space/invoice_".$number.".pdf";
        try {
            $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'fr');
            $html2pdf->setDefaultFont('Arial');
            $html2pdf->writeHTML($content);
            if (getenv("PFM_MODE") == "test") {
                $html2pdf->Output($out, 'F');
            } else {
                $html2pdf->Output($unit . "_" . $resp . "_" . $number . '.pdf');
            }
        } catch (Exception $e) {
            throw new PfmException("Pdf generation error: " . $e, 500);
        }
    }

    /**
     * @param InvoiceContent $content
     * @param string $lang
     * @return string
     */
    private static function mkInvoiceTable(InvoiceContent $content, string $lang): string
    {
         $table = '<table cellspacing="0" style="width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;">
                    <tr>
                        <th style="width: 45%">' . InvoicesTranslator::Designation($lang) . '</th>
                        <th style="width: 14%">' . InvoicesTranslator::Quantity($lang) . '</th>
                        <th style="width: 17%">' . InvoicesTranslator::UnitPrice($lang) . '</th>
                        <th style="width: 24%">' . InvoicesTranslator::Price_HT($lang) . '</th>
                    </tr>
                </table>';

        $table .= '<table cellspacing="0" style="width: 100%; border: solid 1px black; background: #F7F7F7; text-align: center; font-size: 10pt;">';

        foreach ($content->entries as $entry) {
            $table .= '<tr>';

            $table .= '<td style="width: 45%; text-align: left; border: solid 1px black;"><div style="margin-left: 1%;">' . $entry->name . "</div>";
            foreach ($entry->lines as $l)
                $table .= '<div style="margin-left: 5%; font-size: 8pt;">&nbsp;-&nbsp;' . $l->name . "</div>";
            $table .= '</td>';

            $table .= '<td style="width: 14%; border: solid 1px black; text-align: unset;"><div></div>';
            foreach ($entry->lines as $l)
                $table .= '<div style="font-size: 8pt; width: 100%; text-align: center;">' . $l->formattedQuantity . "</div>";
            $table .= '</td>';

            $table .= '<td style="width: 17%; border: solid 1px black; text-align: unset;"><div></div>';
            foreach ($entry->lines as $l)
                $table .= '<div style="font-size: 8pt; width: 100%; text-align: center; ">' . $l->formattedUnitPrice . "</div>";
            $table .= '</td>';

            $table .= '<td style="width: 24%; border: solid 1px black; text-align: unset;"><div></div>';
            $table .= '<table cellspacing="0" style="width: 100%;"><tr>';
            $table .= '<td style="width: 50%; border-right: dashed .5px grey; ">';
            foreach ($entry->lines as $l)
                $table .= '<div style="font-size: 8pt; text-align: center;">' . $l->formattedTotal . "</div>";
            $table .= '</td>';
            $table .= '<td style="width: 50%; vertical-align: middle;"><div style="text-align: right;">' . $entry->formattedTotal . "</div></td>";
            $table .= '</tr></table>';
            $table .= '</td>';

            $table .= '</tr>';
        }

        if ($content->formattedDiscount !== "0") {
            $table .= "<tr>";
            $table .= "<td style=\"width: 52%; text-align: left; border: solid 1px black;\">" . InvoicesTranslator::Discount($lang) . "</td>";
            $table .= "<td style=\"width: 14%; border: solid 1px black;\">" . 1 . "</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . $content->formattedDiscount . " %</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . $content->formattedDiscount . " %</td>";
            $table .= "</tr>";
        }
        $table .= "</table>";

        return $table;
    }

    /**
     * @param array<string> $line
     * @return InvoiceLine
     */
    private static function mkInvoiceLine(array $line): InvoiceLine
    {
        return new InvoiceLine(count($line) > 3 ? " " . $line[3] : ""
                             , $line[2]
                             , $line[1]);
    }
}

final class InvoiceContent {
    public readonly float $total;
    public readonly string $formattedDiscount;

    public function __construct(/** @var array<InvoiceEntry> */public readonly array $entries,
                                                               mixed $mdiscount)
    {
        $total =
            Fp\fold(0.0, $this->entries)(fn(float $acc, InvoiceEntry $entry) => $acc + $entry->total);
        $this->total =
            self::applyDiscount($total, $mdiscount)->getOrElse($total);
        $this->formattedDiscount =
            Option::fromNullable($mdiscount)->flatMap(Fc\asString(...))->getOrElse("0");
    }

    /**
     * @param float $total
     * @param mixed $mdiscount
     * @return Option<float>
     */
    private static function applyDiscount(float $total, mixed $mdiscount): Option
    {
        return Option::do(function() use ($total, $mdiscount) {

            $safeDiscount = yield Option::fromNullable($mdiscount);

            $floatDiscount = yield Fv\proveFloat($safeDiscount);

            return (1 - $floatDiscount / 100) * $total;
        });
    }
}

final class InvoiceEntry {
    public readonly float $total;
    public readonly string $formattedTotal;

    public function __construct(public readonly int    $id,
                                public readonly string $name,
                                /** @var array<InvoiceLine> */
                                public readonly array  $lines)
    {
        $this->total = Fp\fold(0.0, $lines)(fn(float $acc, InvoiceLine $l) => $acc + $l->total);
        $this->formattedTotal = formatNum($this->total, " &euro;");
    }


}

final class InvoiceLine {
    public readonly float $total;
    public readonly string $formattedQuantity;
    public readonly string $formattedUnitPrice;
    public readonly string $formattedTotal;

    public function __construct(public readonly string $name,
                                public readonly float $unitPrice,
                                public readonly float $quantity){
        $this->total = $quantity * $unitPrice;
        $this->formattedQuantity = formatNum($quantity);
        $this->formattedUnitPrice = formatNum($unitPrice, " &euro;");
        $this->formattedTotal = formatNum($this->total, " &euro;");
    }

    public function withName(string $newName): InvoiceLine
    {
        return new InvoiceLine($newName, $this->unitPrice, $this->quantity);
    }
}

 function formatNum(float $quantity, string $suffix = ""): string
 {
     return number_format($quantity, 2, ',', ' ') . $suffix;
 }
