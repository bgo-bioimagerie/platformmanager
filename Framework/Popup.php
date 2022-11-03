<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Popup
 *
 * @author sprigent
 */
class Popup
{
    private $windowsActionId;
    private $windowshtml;
    private $useClassOrID;

    public function __construct()
    {
    }

    public function addWindow($actionID, $html, $classOrid = "id")
    {
        $this->windowsActionId[] = $actionID;
        $this->windowshtml[] = $html;
        if ($classOrid == "class") {
            $this->useClassOrID[] = ".";
        } else {
            $this->useClassOrID[] = "#";
        }
    }

    public function render($renderEmptyLinkss = false)
    {
        $html = "";
        if ($renderEmptyLinkss) {
            $html .= $this->renderEmptyLinks();
        }
        $html .= $this->renderWindows();
        $html .= $this->renderJS();
        echo $html;
    }

    protected function renderWindows()
    {
        $html = "<div id=\"hider\" class=\"col-12\"></div> ";
        for ($i = 0; $i < count($this->windowsActionId); $i++) {
            $html .= $this->renderWindow($this->windowsActionId[$i], $this->windowshtml[$i]);
        }
        return $html;
    }

    protected function renderWindow($actionID, $html)
    {
        $renderHtml = "<div id=\"".$actionID."popup_box\" class=\"pm_popup_box\" style=\"display: none;\"> "
                . "<div class=\"col-1 offset-11\" style=\"text-align: right;\"><a id=\"".$actionID."buttonclose\" class=\"bi-x-circle-fill\"></a></div>"
                . $html
                . "</div>";
        return $renderHtml;
    }

    protected function renderJS()
    {
        $html = "<script>$(document).ready(function () {";
        $html .= "$(\"#hider\") . hide();";
        for ($i = 0; $i < count($this->windowsActionId); $i++) {
            $html .= $this->renderWindowJS($this->useClassOrID[$i], $this->windowsActionId[$i]);
        }
        $html .= "});</script>";
        return $html;
    }

    protected function renderWindowJS($classOrid, $actionID)
    {
        $renderHtml = "$(\"#".$actionID."popup_box\") . hide(); ";

        //on click show the hider div and the message
        $renderHtml .= "$(\"".$classOrid.$actionID."\") . click(function () { ";
        //$renderHtml .= "alert(\"clicked detected\");";
        $renderHtml .= " $(\"#hider\") . fadeIn(\"slow\");";
        $renderHtml .= " $('#".$actionID."popup_box') . fadeIn(\"slow\");";
        $renderHtml .= "        }); ";
        //on click hide the message and the
        $renderHtml .= "$(\"#".$actionID."buttonclose\") . click(function () { ";
        //$renderHtml .= "alert(\"clicked detected\");";
        $renderHtml .= " $(\"#hider\") . hide();";
        $renderHtml .= " $('#".$actionID."popup_box') . hide();";
        $renderHtml .= " });";
        return $renderHtml;
    }

    protected function renderEmptyLinks()
    {
        $html = "";
        for ($i = 0; $i < count($this->windowsActionId); $i++) {
            $html .= "<a id=\"".$this->windowsActionId[$i]."\"></a>";
        }
        return $html;
    }
}
