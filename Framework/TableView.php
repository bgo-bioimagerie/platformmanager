<?php

require_once 'Framework/Request.php';

/**
 * Class allowing to generate and check a form html view. 
 * 
 * @author Sylvain Prigent
 */
class TableView {

    private $title;
    private $editURL;
    private $editIndex;
    private $useSearchVal;
    private $printAction;
    private $isprint;
    private $deleteURL;
    private $deleteIndex;
    private $deleteNameIndex;
    private $ignoredEntryKey;
    private $ignoredEntryValue;
    private $linesButtonActions;
    private $linesButtonActionsIndex;
    private $linesButtonName;
    private $colorIndexes;
    private $textMaxLength;
    private $numFixedCol;

    /**
     * Constructor
     */
    public function __construct() {
        $this->title = "";
        $this->useSearchVal = true;
        $this->isprint = false;
        $this->deleteURL = "";
        $this->ignoredEntryKey = "";
        $this->ignoredEntryValue = "";
        $this->linesButtonActions = array();
        $this->linesButtonActionsIndex = array();
        $this->linesButtonName = array();
        $this->colorIndexes = array();
        $this->exportAction = "";
        $this->iscsv = false;
        $this->textMaxLength = 0;
        $this->numFixedCol = 0;
    }

    /**
     * Set the table title
     */
    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function setFixedColumnsNum($num){
        $this->numFixedCol = $num;
    }
    

    public function setTextMaxLength($value) {
        $this->textMaxLength = $value;
    }

    public function addLineEditButton($editURL, $editIndex = "id") {
        $this->editURL = $editURL;
        $this->editIndex = $editIndex;
    }

    public function addDeleteButton($deleteURL, $deleteIndex = "id", $deleteNameIndex = "name") {
        $this->deleteURL = $deleteURL;
        $this->deleteIndex = $deleteIndex;
        $this->deleteNameIndex = $deleteNameIndex;
    }

    public function addLineButton($action, $actionIndex = "id", $buttonTitle = "edit") {
        $this->linesButtonActions[] = $action;
        $this->linesButtonActionsIndex[] = $actionIndex;
        $this->linesButtonName[] = $buttonTitle;
    }

    public function ignoreEntry($key, $value) {
        $this->ignoredEntryKey = $key;
        $this->ignoredEntryValue = $value;
    }

    public function useSearch($value) {
        $this->useSearchVal = $value;
    }

    public function addPrintButton($action) {
        $this->printAction = $action;
    }

    public function addExportButton($action) {
        $this->exportAction = $action;
    }

    public function setColorIndexes($indexesArray) {
        $this->colorIndexes = $indexesArray;
    }

    public function isPrint() {
        $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        //echo "url = " . $actual_link . "<br/>";
        if (strstr($actual_link, 'print=1')) {
            $this->isprint = true;
            return true;
        }
        return false;
    }

    public function isExport() {
        $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        // echo "url = " . $actual_link . "<br/>";
        if (strstr($actual_link, 'csv=1')) {
            $this->iscsv = true;
            return true;
        }
        return false;
    }

    /**
     * Generate a basic table view
     * @param array $data table data ( 'key' => value)
     * @param array $headers table headers ( 'key' => 'headername' )
     */
    public function view($data, $headers) {

        $html = "";
        if ($this->isPrint()) {
            $rootWeb = Configuration::get("rootWeb", "/");
            $html .= "<head>";
            $html .= "<meta charset=\"UTF-8\" />";
            $html .= "<base href=\"" . $rootWeb . "\">";
            $html .= "<link rel=\"stylesheet\" href=\"externals/bootstrap/css/bootstrap.min.css\">";
            $html .= "</head>";
        }

        if ($this->useSearchVal && !$this->isprint) {
            $headerCount = count($headers);
            if ($this->editURL != "") {
                $headerCount++;
            }
            if ($this->deleteURL != "") {
                $headerCount++;
            }
            $headerCount += count($this->linesButtonActions);
            $html = $this->addSearchHeader($html, $headerCount);
        }

        if ($this->printAction != "" && $this->exportAction != "" && !$this->isprint) {
            $html .= "<div class=\"col-xs-2 col-xs-offset-10\">";
            // echo "redirect to : " . $this->printAction."?print=1" . "<br/>";
            $html .= "<button type='button' onclick=\"location.href='" . $this->printAction . "?print=1'\" class=\"btn btn-default\">Print</button>";
            $html .= "<button type='button' onclick=\"location.href='" . $this->exportAction . "?csv=1'\" class=\"btn btn-default\">Export</button>";
            $html .= "</div>";
        } else {
            if ($this->printAction != "" && !$this->isprint) {
                $html .= "<div class=\"col-xs-2 col-xs-offset-10\">";
                // echo "redirect to : " . $this->printAction."?print=1" . "<br/>";
                $html .= "<button type='button' onclick=\"location.href='" . $this->printAction . "?print=1'\" class=\"btn btn-default\">Print</button>";
                $html .= "</div>";
            }
            if ($this->exportAction != "" && !$this->isprint) {
                $html .= "<div class=\"col-xs-2 col-xs-offset-10\">";
                // echo "redirect to : " . $this->printAction."?print=1" . "<br/>";
                $html .= "<button type='button' onclick=\"location.href='" . $this->exportAction . "?csv=1'\" class=\"btn btn-default\">Export</button>";
                $html .= "</div>";
            }
        }

        if ($this->title != "") {
            $html .= "<div class=\"page-header\">";
            $html .= "<h1>" . $this->title . "</h1>";
            $html .= "</div>";
        }

        $html .= "<table id=\"dataTable\" class=\"table table-bordered table-striped\" cellspacing=\"0\" width=\"100%\">";

        // table header
        $html .= "<thead>";
        $html .= "<tr>";
        
        foreach ($headers as $key => $value) {
            $html .= "<th>" . $value . "</th>";
        }
        if ($this->editURL != "" && !$this->isprint) {
            $html .= "<th></th>";
        }
        if ($this->deleteURL != "" && !$this->isprint) {
            $html .= "<th></th>";
        }
        if (count($this->linesButtonActions) > 0) {
            for ($lb = 0; $lb < count($this->linesButtonActions); $lb++) {
                $html .= "<th></th>";
            }
        }
        $html .= "</tr>";
        $html .= "</thead>";

        // table body			
        $html .= "<tbody>";
        foreach ($data as $dat) {
            if ($this->printIt($dat)) {
                $html .= "<tr>";
                foreach ($headers as $key => $value) {

                    $ccolor = "#ffffff";
                    if (isset($this->colorIndexes[$key])) {
                        $ccolor = $dat[$this->colorIndexes[$key]];
                    } else {
                        if (isset($this->colorIndexes["all"])) {
                            $ccolor = $dat[$this->colorIndexes["all"]];
                        }
                    }
                    $val = $dat[$key];
                    if (count($dat[$key]) && $this->textMaxLength > 0) {
                        $val = substr($dat[$key], 0, $this->textMaxLength);
                    }
                    $html .= "<td style=\"background-color:" . $ccolor . ";\"> " . htmlspecialchars($val, ENT_QUOTES, 'UTF-8', false) . "</td>";
                }
                if ($this->editURL != "" && !$this->isprint) {
                    $idxVal = "";
                    if ($this->editIndex != "") {
                        $idxVal = $dat[$this->editIndex];
                    }

                    $html .= "<td style=\"width:12px;\">" . "<button type='button' onclick=\"location.href='" . $this->editURL . "/" . $idxVal . "'\" class=\"btn btn-xs btn-primary\">Edit</button>" . "</td>";
                }
                if ($this->deleteURL != "" && !$this->isprint) {
                    $html .= "<td style=\"width:12px;\">" . $this->addDeleteButtonHtml($dat[$this->deleteIndex], $dat[$this->deleteNameIndex]) . "</td>";
                }
                if (count($this->linesButtonActions) > 0) {
                    for ($lb = 0; $lb < count($this->linesButtonActions); $lb++) {
                        $html .= "<td style=\"width:12px;\">" . "<button type='button' onclick=\"location.href='" . $this->linesButtonActions[$lb] . "/" . $dat[$this->linesButtonActionsIndex[$lb]] . "'\" class=\"btn btn-xs btn-default\">" . $this->linesButtonName[$lb] . "</button>" . "</td>";
                    }
                }
                $html .= "</tr>";
            }
        }
        $html .= "</tbody>";
        $html .= "</table>";

        return $html;
    }

    private function printIt($dat) {
        if ($this->ignoredEntryKey != "") {
            if ($dat[$this->ignoredEntryKey] == $this->ignoredEntryValue) {
                return false;
            }
            return true;
        }
        return true;
    }

    private function addSearchHeader($html, $headerscount) {
        
        $js = file_get_contents("Framework/TableScript.php");
        return str_replace("numFixedCol", $this->numFixedCol, $js);
        
        $html .= "<head>";

        $html .= "<link rel=\"stylesheet\" href=\"externals/dataTables/dataTables.bootstrap.css\">";
        $html .= "<link rel=\"stylesheet\" href=\"externals/dataTables/dataTables.fixedHeader.css\">";

        $html .= "<script src=\"externals/jquery-1.11.1.js\"></script>";
        $html .= "<script src=\"externals/dataTables/jquery.dataTables.min.js\"></script>";
        $html .= "<script src=\"externals/dataTables/dataTables.fixedHeader.min.js\"></script>";
        $html .= "<script src=\"externals/dataTables/dataTables.bootstrap.js\"></script>";

        $html .= "<style>";
        //$html .= "body { font-size: 120%; padding: 1em; margin-top:30px; margin-left: -15px;}";
        $html .= "div.FixedHeader_Cloned table { margin: 0 !important }";

        $html .= "table{";

        $html .= "	white-space: nowrap;";
        $html .= "}";

        $html .= "thead tr{";
        $html .= "	height: 100px;";
        $html .= "}";

        $html .= "thead th{";
        $html .= "	vertical-align:bottom; text-align:center;";
        $html .= "}";

        $html .= "</style>";

        $html .= "<script>";
        $html .= "$(document).ready( function() {";
        $html .= "$('#dataTable').dataTable( {";
        $html .= "\"aoColumns\": [";

        for ($c = 0; $c < $headerscount; $c++) {
            if ($c == $headerscount - 1) {
                $html .= "{ \"bSearchable\": true }";
            } else if ($c == 1) {
                $html .= "null,";
            } else {
                $html .= "{ \"bSearchable\": true },";
            }
        }
        $html .="],";
        $html .= "\"lengthMenu\": [[100, 200, 300, -1], [100, 200, 300, \"All\"]]";
        $html .= "}";
        $html .= ");";
        $html .="} );";
        $html .="</script>";

        $html .="<script>";
        $html .="$(document).ready(function() {";
        $html .="	var table = $('#dataTable').DataTable();";
        $html .="	new $.fn.dataTable.FixedHeader( table, {";
        $html .="		alwaysCloneTop: true";
        $html .="	});";

        $html .="} );";
        $html .="</script>";

        $html .= "</head>";

        return $html;
    }

    private function addDeleteButtonHtml($id, $name) {

        $html = $this->addDeleteScript($id, $name);
        //$html = "";
        $html = $html . "<input class=\"btn btn-xs btn-danger\" type=\"button\" onclick=\"ConfirmDelete" . $id . "()\" value=\"Delete\">";
        return $html;
    }

    private function addDeleteScript($id, $name) {
        $html = "<script type=\"text/javascript\">";
        $html .= "function ConfirmDelete" . $id . "()";
        $html .= "{";
        $html .= "	if (confirm(\"Delete " . $name . " ?\"))";
        $html .= "		location.href='" . $this->deleteURL . "/" . $id . "';";
        $html .= "	}";
        $html .= "</script>";
        return $html;
    }

    /**
     * Generate a basic table view
     *
     * @param array $data
     *        	table data ( 'key' => value)
     * @param array $headers
     *        	table headers ( 'key' => 'headername' )
     */
    public function exportCsv($data, $headers) {

        $csv = "";

        // table header
        foreach ($headers as $key => $value) {
            $csv .= $value . ";";
        }
        $csv .= "\r\n";
        // table body
        foreach ($data as $dat) {
            if ($this->printIt($dat)) {
                foreach ($headers as $key => $value) {
                    $csv .= htmlspecialchars($dat [$key], ENT_QUOTES, 'UTF-8', false) . ";";
                }
                $csv .= "\r\n";
            }
        }

        header("Content-Type: application/csv-tab-delimited-table");
        header("Content-disposition: filename=export.csv");
        echo $csv;
    }

}
