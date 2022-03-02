<?php

require_once 'Framework/Request.php';
require_once 'Framework/Constants.php';

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
     * @param bool $small display bootstrap small table
     */
    public function view($data, $headers, $small=false) {

        $html = "";
        if ($this->isPrint()) {
            $rootWeb = Configuration::get("rootWeb", "/");
            $html .= "<head>";
            $html .= "<meta charset=\"UTF-8\" />";
            $html .= "<base href=\"" . $rootWeb . "\">";
            $html .= "<link rel=\"stylesheet\" href=\"/externals/bootstrap/css/bootstrap.min.css\">";
            $html .= "</head>";
        }
        else{
            $html .= $this->addHeader();
        }

        if ($this->printAction != "" && $this->exportAction != "" && !$this->isprint) {
            $html .= "<div class=\"col-2 offset-10\">";
            $html .= "<button type='button' onclick=\"location.href='" . $this->printAction . "?print=1'\" class=\"btn btn-outline-dark\">Print</button>";
            $html .= "<button type='button' onclick=\"location.href='" . $this->exportAction . "?csv=1'\" class=\"btn btn-outline-dark\">Export</button>";
            $html .= "</div>";
        } else {
            if ($this->printAction != "" && !$this->isprint) {
                $html .= "<div class=\"col-2 offset-10\">";
                $html .= "<button type='button' onclick=\"location.href='" . $this->printAction . "?print=1'\" class=\"btn btn-outline-dark\">Print</button>";
                $html .= "</div>";
            }
            if ($this->exportAction != "" && !$this->isprint) {
                $html .= "<div class=\"col-2 offset-10\">";
                $html .= "<button type='button' onclick=\"location.href='" . $this->exportAction . "?csv=1'\" class=\"btn btn-outline-dark\">Export</button>";
                $html .= "</div>";
            }
        }

        if ($this->title != "") {
            $html .= "<div class=\"page-header\">";
            $html .= "<h" . $this->titleLevel . ">" . $this->title . "</h" . $this->titleLevel . ">";
            $html .= "</div>";
        }

        $is_small = '';
       if($small) {
            $is_small = ' table-sm ';
       }
        $html .= "<div class=\"table-responsive\"><table id=\"".$this->tableID."\" class=\"table $is_small table-bordered table-striped\" cellspacing=\"0\" width=\"100%\">";

        $isButtons = $this->isButtons();
        // table header
        $html .= "<thead>";
        $html .= "<tr>";

        for ($b = 0 ; $b < $isButtons ; $b++){
            $html .= "<th class=\"no-sort\"></th>";
        }        
        
        if ($this->downloadButton != "") {
            $html .= "<th aria-label=\"download\"></th>";
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

        $addDelete = false;
        
        foreach ($data as $dat) {
            if ($this->printIt($dat)) {
                $html .= "<tr>";
                
                if (count($this->linesButtonActions) > 0 && !$this->isprint) {
                    for ($lb = 0; $lb < count($this->linesButtonActions); $lb++) {
                        $html .= '<td style="width: 1%; white-space: nowrap;">';
                        $html .= "<button type='button' onclick=\"location.href='" . $this->linesButtonActions[$lb] . "/" . $dat[$this->linesButtonActionsIndex[$lb]] . "'\" class=\"btn btn-sm btn-outline-dark\">" . $this->linesButtonName[$lb] . "</button><span> </span>";
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
                        $html .= "<button onclick=\"editentry('".$this->editURL . "_" . $idxVal."')\" id=\"".$this->editURL . "_" . $idxVal."\" type='button' class=\"btn btn-sm btn-primary\">Edit</button><span> </span>" ;
                    }
                    else{
                         $html .= "<button type='button' onclick=\"location.href='" . $this->editURL . "/" . $idxVal . "'\" class=\"btn btn-sm btn-primary\">Edit</button><span> </span>";   
                    }  
                    $html .= "</td>";
                }
                if ($this->deleteURL != "" && !$this->isprint) {
                    $html .= '<td style="width: 1%; white-space: nowrap;">';
                    $html .= $this->addDeleteButtonHtml($dat[$this->deleteIndex], $dat[$this->deleteNameIndex]);
                    $html .= "</td>";
                    $addDelete = true;
                }

                if ($this->downloadButton != "") {
                    $html .= $this->addDownloadButtonHtml($dat[$this->downloadButton]);
                }
                foreach ($headers as $key => $value) {

                    $ccolor = '';
                    if (isset($this->colorIndexes[$key])){  
                        $ccolor = $dat[$this->colorIndexes[$key]];
                    }
                    else{
                        if (isset($this->colorIndexes["all"])){
                            $ccolor = $dat[$this->colorIndexes["all"]];
	    		        }
                    }
                    $tcolor = '';
                    if(isset($this->colorIndexes["all_text"])) {
                        $tcolor = $dat[$this->colorIndexes["all_text"]];
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
                                $html .= sprintf('<a target="_blank" rel="noreferrer,noopener"  href="%s"><button type="btn btn-outline-dark">%s</button></a>', $val, $value["text"]);
                            }
                            $html .= '</td>';
                        }
                    }
                    else{
                        if (strlen($dat[$key]) && $this->textMaxLength > 0) {
                            $val = substr($dat[$key], 0, $this->textMaxLength);
                        }
                        $html .= "<td style=\"background-color: $ccolor; color: $tcolor\"> " . htmlspecialchars($val, ENT_QUOTES, 'UTF-8', false) . "</td>";
                    }
                }
                $html .= "</tr>";
            }
        }
        $html .= "</tbody>";
        $html .= "</table></div>";
        if($addDelete) {
            $html .= $this->addDeleteScript();
        }

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
        $col = 0;
        if ($this->downloadButton != "") {
            $col++;
        }
        if(count($this->linesButtonActions) > 0 && !$this->isprint) {
            $col+=count($this->linesButtonActions);
        }
        $str1 = str_replace('let defaultCol = 0;','let defaultCol = '.$col.';', $str1);
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
    }

    /**
     * 
     * @param type $url
     * @return string
     */
    private function addDownloadButtonHtml($url) {
        if(!$url) {
            return '<td></td>';
        }
        $html = "<td>" . "<button type='button' onclick=\"location.href='" . $url . "'\" class=\"btn btn-sm btn-outilne-dark\"> <span class=\"bi-download\" aria-hidden=\"true\"></span> </button>" . "</td>";

        return $html;
    }

    /**
     * 
     * @param type $id
     * @param type $name
     * @return string
     */
    private function addDeleteButtonHtml($id, $name) {
        return "<input class=\"btn btn-sm btn-danger\" type=\"button\" onclick=\"ConfirmDelete($id, '$name')\" value=\"Delete\">";
    }

    /**
     * 
     * @param type $id
     * @param type $name
     * @return string
     */
    private function addDeleteScript() {
        $html = "<script type=\"text/javascript\">";
        $html .= "function ConfirmDelete(id, name)";
        $html .= "{";
        $html .= '	if (confirm(`Delete ${name} ?`))';
        $html .= '		location.href=`'.$this->deleteURL . '/${id}`;';
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
