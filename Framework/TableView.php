<?php

require_once 'Framework/Request.php';

/**
 * Class allowing to generate and check a form html view. 
 * 
 * @author Sylvain Prigent
 */
class TableView {

    private $title;
    private $titleLevel;
    private $editURL;
    private $editIndex;
    private $editJS;
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
    private $downloadButton;
    private $tableID;

    /**
     * Constructor
     */
    public function __construct($tableID = "dataTable") {
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
        $this->downloadButton = "";
        $this->tableID = $tableID;
    }

    /**
     * Set the table title
     */
    public function setTitle($title, $level = 3) {
        $this->title = $title;
        $this->titleLevel = $level;
    }

    /**
     * 
     * @param type $num
     */
    public function setFixedColumnsNum($num) {
        $this->numFixedCol = $num;
    }

    /**
     * 
     * @param int $value
     */
    public function setTextMaxLength($value) {
        $this->textMaxLength = $value;
    }

    /**
     * 
     * @param type $editURL
     * @param type $editIndex
     */
    public function addLineEditButton($editURL, $editIndex = "id", $editJS = false) {
        $this->editURL = $editURL;
        $this->editIndex = $editIndex;
        $this->editJS = $editJS;
    }

    /**
     * 
     * @param type $urlIndex
     */
    public function addDownloadButton($urlIndex) {
        $this->downloadButton = $urlIndex;
    }

    /**
     * 
     * @param type $deleteURL
     * @param type $deleteIndex
     * @param type $deleteNameIndex
     */
    public function addDeleteButton($deleteURL, $deleteIndex = "id", $deleteNameIndex = "name") {
        $this->deleteURL = $deleteURL;
        $this->deleteIndex = $deleteIndex;
        $this->deleteNameIndex = $deleteNameIndex;
    }

    /**
     * 
     * @param type $action
     * @param type $actionIndex
     * @param type $buttonTitle
     */
    public function addLineButton($action, $actionIndex = "id", $buttonTitle = "edit") {
        $this->linesButtonActions[] = $action;
        $this->linesButtonActionsIndex[] = $actionIndex;
        $this->linesButtonName[] = $buttonTitle;
    }

    /**
     * 
     * @param type $key
     * @param type $value
     */
    public function ignoreEntry($key, $value) {
        $this->ignoredEntryKey = $key;
        $this->ignoredEntryValue = $value;
    }

    /**
     * 
     * @param type $value
     */
    public function useSearch($value) {
        $this->useSearchVal = $value;
    }

    /**
     * 
     * @param type $action
     */
    public function addPrintButton($action) {
        $this->printAction = $action;
    }

    /**
     * 
     * @param type $action
     */
    public function addExportButton($action) {
        $this->exportAction = $action;
    }

    /**
     * 
     * @param type $indexesArray
     */
    public function setColorIndexes($indexesArray) {
        $this->colorIndexes = $indexesArray;
    }

    /**
     * 
     * @return boolean
     */
    public function isPrint() {
        $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        //echo "url = " . $actual_link . "<br/>";
        if (strstr($actual_link, 'print=1')) {
            $this->isprint = true;
            return true;
        }
        return false;
    }

    /**
     * 
     * @return boolean
     */
    public function isExport() {
        $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        // echo "url = " . $actual_link . "<br/>";
        if (strstr($actual_link, 'csv=1')) {
            $this->iscsv = true;
            return true;
        }
        return false;
    }
    
    protected function isButtons(){
        $count = 0;
        if (count($this->linesButtonActions) > 0){
            $count += count($this->linesButtonActions);
        }
        if ($this->editURL != ""){
            $count ++;
        }
        if ($this->deleteURL != ""){
            $count++;
        }
        return $count;
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
        else{
            $html .= $this->addHeader();
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
            $html .= "<h" . $this->titleLevel . ">" . $this->title . "</h" . $this->titleLevel . ">";
            $html .= "</div>";
        }

       
        $html .= "<table id=\"".$this->tableID."\" class=\"table table-bordered table-striped\" cellspacing=\"0\" width=\"100%\">";
        //$html .= "<table id=\"example\" class=\"table table-striped table-bordered nowrap\" cellspacing=\"0\" width=\"100%\">";

        $isButtons = $this->isButtons();
        // table header
        $html .= "<thead>";
        $html .= "<tr>";

        for ($b = 0 ; $b < $isButtons ; $b++){
            $html .= "<th></th>";
        }
        //if ($isButtons){
        //    $html .= "<th></th>";
        //}
        
        
        if ($this->downloadButton != "") {
            $html .= "<th></th>";
        }
        foreach ($headers as $key => $value) {
            $title = "";
            if(is_array($value)){
                $title = $value["title"];
            }
            else{
                $title = $value;
            }
            $html .= "<th>" . $title . "</th>";
        }
        $html .= "</tr>";
        $html .= "</thead>";

        // table body			
        $html .= "<tbody>";
        
        foreach ($data as $dat) {
            if ($this->printIt($dat)) {
                $html .= "<tr>";
                
                
                //if ($isButtons){
                //    $html .= "<td>";
                // }
                
                if (count($this->linesButtonActions) > 0 && !$this->isprint) {
                    for ($lb = 0; $lb < count($this->linesButtonActions); $lb++) {
                        $html .= '<td style="width: 1%; white-space: nowrap;">';
                        $html .= "<button type='button' onclick=\"location.href='" . $this->linesButtonActions[$lb] . "/" . $dat[$this->linesButtonActionsIndex[$lb]] . "'\" class=\"btn btn-xs btn-default\">" . $this->linesButtonName[$lb] . "</button><span> </span>";
                        $html .= "</td>";
                    }
                }

                if ($this->editURL != "" && !$this->isprint) {
                    $idxVal = "";
                    if ($this->editIndex != "") {
                        $idxVal = $dat[$this->editIndex];
                    }
                    $html .= '<td style="width: 1%; white-space: nowrap;">';
                    if($this->editJS){
                        $html .= "<button id=\"".$this->editURL . "_" . $idxVal."\" type='button' class=\"btn btn-xs btn-primary\">Edit</button><span> </span>" ;
                    }
                    else{
                         $html .= "<button type='button' onclick=\"location.href='" . $this->editURL . "/" . $idxVal . "'\" class=\"btn btn-xs btn-primary\">Edit</button><span> </span>";   
                    }  
                    $html .= "</td>";
                }
                if ($this->deleteURL != "" && !$this->isprint) {
                    $html .= '<td style="width: 1%; white-space: nowrap;">';
                    $html .= $this->addDeleteButtonHtml($dat[$this->deleteIndex], $dat[$this->deleteNameIndex]);
                    $html .= "</td>";
                }
                
                //if ($isButtons){
                //    $html .= "</td>";
                //}
                
                

                if ($this->downloadButton != "") {
                    $html .= $this->addDownloadButtonHtml($dat[$this->downloadButton]);
                }
                foreach ($headers as $key => $value) {

                    $ccolor = "#ffffff";
                    if (isset($this->colorIndexes[$key])){  
                        $ccolor = $dat[$this->colorIndexes[$key]];
                    }
                    else{
                        if (isset($this->colorIndexes["all"])){
                            $ccolor = $dat[$this->colorIndexes["all"]];
	    		        }
                    }
                    
                    $val = $dat[$key];
                    if(is_array($value)){
                        if($value["type"] == "image"){
                            if($val != ""){
                                $url = $value["base_url"] . $val;
                                $html .= '<td style="background-color:"' .$ccolor.';">  <img src="'.$url.'" alt="img" height="42" width="42"></td>';
                            }
                            else{
                                   $html .= '<td style="background-color:"' .$ccolor.';"> </td>';
                            }
                        }
                        else if($value["type"] == "glyphicon"){
                            $html .= '<td><span class="'.$val.'" aria-hidden="true" style="color:'.$value["color"].'"></span></td>';
                        }
                        else if ($value["type"] == "download"){
                            $html .= '<td>';
                            if ( $val != "" ){
                                $html .= "<form role=\"form\" id=\"tabledownload\" class=\"form-horizontal\" action=\"".$value["action"]."\" method=\"POST\">";
                                $html .= "<input name=\"filetransferurl\" type=\"hidden\" value=\"".$val."\">";
                                $html .= "<input type=\"submit\" class=\"btn btn-default\" value=\"" . $value["text"] . "\" />";
                                $html .= "</form>";
                            }
                            $html .= '</td>';
                        }
                    }
                    else{
                        if (strlen($dat[$key]) && $this->textMaxLength > 0) {
                            $val = substr($dat[$key], 0, $this->textMaxLength);
                        }
                        $html .= "<td style=\"background-color:" .$ccolor.";\"> " . htmlspecialchars($val, ENT_QUOTES, 'UTF-8', false) . "</td>";
                    }
                }
                $html .= "</tr>";
            }
        }
        $html .= "</tbody>";
        $html .= "</table>";

        return $html;
    }

    /**
     * 
     * @param type $dat
     * @return boolean
     */
    private function printIt($dat) {
        if ($this->ignoredEntryKey != "") {
            if ($dat[$this->ignoredEntryKey] == $this->ignoredEntryValue) {
                return false;
            }
            return true;
        }
        return true;
    }

    private function addHeader(){
        $js = file_get_contents("Framework/TableScript.php");
        $str1 = str_replace("numFixedCol", $this->numFixedCol, $js);
        return str_replace("tableID", $this->tableID, $str1);
    }
    
    /**
     * 
     * @param type $html
     * @param int $headerscount
     * @return string
     */
    private function addSearchHeader($html, $headerscount) {

        $js = file_get_contents("Framework/TableScript.php");
        $str1 = str_replace("numFixedCol", $this->numFixedCol, $js);
        return str_replace("tableID", $this->tableID, $str1);
         

        $html .= "<head>";

        $html .= "<link rel=\"stylesheet\" href=\"externals/dataTables/dataTables.bootstrap.css\">";
        $html .= "<link rel=\"stylesheet\" href=\"externals/dataTables/dataTables.fixedHeader.css\">";

        $html .= "<script src=\"externals/jquery-1.11.1.js\"></script>";
        $html .= "<script src=\"externals/dataTables/jquery.dataTables.min.js\"></script>";
        $html .= "<script src=\"externals/dataTables/dataTables.fixedHeader.min.js\"></script>";
        $html .= "<script src=\"externals/dataTables/dataTables.bootstrap.js\"></script>";
        
        $html .= "<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css\">";
        $html .= "<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/fixedcolumns/3.2.2/css/fixedColumns.bootstrap.min.css\">";

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
        $html .= "$('#" . $this->tableID . "').dataTable( {";
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
        $html .="	var table = $('#" . $this->tableID . "').DataTable();";
        $html .="	new $.fn.dataTable.FixedHeader( table, {";
        $html .="		alwaysCloneTop: true";
        $html .="	});";

        $html .="} );";
        $html .="</script>";

        $html .= "</head>";

        return $html;
    }

    /**
     * 
     * @param type $url
     * @return string
     */
    private function addDownloadButtonHtml($url) {
        $html = "<td>" . "<button type='button' onclick=\"location.href='" . $url . "'\" class=\"btn btn-xs btn-default\"> <span class=\"glyphicon glyphicon-open\" aria-hidden=\"true\"></span> </button>" . "</td>";

        return $html;
    }

    /**
     * 
     * @param type $id
     * @param type $name
     * @return string
     */
    private function addDeleteButtonHtml($id, $name) {

        $html = $this->addDeleteScript($id, $name);
        $html = $html . "<input class=\"btn btn-xs btn-danger\" type=\"button\" onclick=\"ConfirmDelete" . $id . "()\" value=\"Delete\">";
        return $html;
    }

    /**
     * 
     * @param type $id
     * @param type $name
     * @return string
     */
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
