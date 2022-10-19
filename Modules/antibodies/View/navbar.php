<?php
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$ecmenucolor = "";
$ecmenucolortxt = "";
if ($ecmenucolor == "") {
    $ecmenucolor = "#f1f1f1";
}
if ($ecmenucolortxt == "") {
    $ecmenucolortxt = "#000";
}
?>
    <style>
        #menu-button-div a{
            font: 12px Arial,sans-serif;
            text-decoration: none;
            color: #333333;
            padding-left: 12px;
            /* padding: 2px 6px 2px 6px; */
        }

        #menu-button-div{
            margin-top: -2px;
            /* padding: 2px 6px 2px 6px; */
        }

        #menu-button-div:hover{
            font: 12px Arial,sans-serif;
            text-decoration: none;
            background-color: #e1e1e1;
            color: #333333;
            padding: 2px 2px 2px 2px;
        }

        #separatorp{
            padding-top: 12px;
            text-transform: uppercase; 
            font-weight: bold; 
            font-size: 11px;
            color: #616161;
        }
    </style>

<div style="padding: 7px; background-color: <?php echo $ecmenucolor ?>; color:<?php echo $ecmenucolortxt ?>;">

    <div class="col-12" style="margin-top: 0px;">

        <h4 style="text-transform: uppercase;">Anticorps</h4>

    </div>
        
    <div class="col-12">
        <p id="separatorp">Listing</p>

        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="anticorps/<?php echo $idSpace ?>">Anticorps</a>
            <a href="anticorpsedit/<?php echo $idSpace ?>/0">+</a> 
        </div>	
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="protocols/<?php echo $idSpace ?>/id">Protocoles</a>
            <a href="protocolsedit/<?php echo $idSpace ?>/0">+</a>
        </div>
    </div>

    <div class="col-12">
        <p id="separatorp">Référence</p>

        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="sources/<?php echo $idSpace ?>">Sources</a>
            <a href="sourcesedit/<?php echo $idSpace ?>/0">+</a> 
        </div>	
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="isotypes/<?php echo $idSpace ?>">Isotypes</a>
            <a href="isotypesedit/<?php echo $idSpace ?>/0">+</a>
        </div>
    </div>
    
        <div class="col-12">
        <p id="separatorp">Tissus</p>

        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="especes/<?php echo $idSpace ?>">Espèces</a>
            <a href="especesedit/<?php echo $idSpace ?>/0">+</a> 
        </div>	
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="organes/<?php echo $idSpace ?>">Organes</a>
            <a href="organesedit/<?php echo $idSpace ?>/0">+</a>
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="prelevements/<?php echo $idSpace ?>">Prélèvements</a>
            <a href="prelevementsedit/<?php echo $idSpace ?>/0">+</a>
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="status/<?php echo $idSpace ?>">Status</a>
            <a href="statusedit/<?php echo $idSpace ?>/0">+</a>
        </div>
    </div>

    <div class="col-12">
        <p id="separatorp">Détails Proto</p>

        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="kit/<?php echo $idSpace ?>">KIT</a>
            <a href="kitedit/<?php echo $idSpace ?>/0">+</a> 
        </div>	
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="proto/<?php echo $idSpace ?>">Proto</a>
            <a href="protoedit/<?php echo $idSpace ?>/0">+</a> 
        </div>	
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="fixative/<?php echo $idSpace ?>">Fixative</a>
            <a href="fixativeedit/<?php echo $idSpace ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="option/<?php echo $idSpace ?>">Option</a>
            <a href="optionedit/<?php echo $idSpace ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="enzymes/<?php echo $idSpace ?>">Enzyme</a>
            <a href="enzymesedit/<?php echo $idSpace ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="dem/<?php echo $idSpace ?>">Dém</a>
            <a href="demedit/<?php echo $idSpace ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="aciinc/<?php echo $idSpace ?>">AcI Inc</a>
            <a href="aciincedit/<?php echo $idSpace ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="linker/<?php echo $idSpace ?>">Linker</a>
            <a href="linkeredit/<?php echo $idSpace ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="inc/<?php echo $idSpace ?>">Linker Inc</a>
            <a href="incedit/<?php echo $idSpace ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="acii/<?php echo $idSpace ?>">acII</a>
            <a href="aciiedit/<?php echo $idSpace ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="application/<?php echo $idSpace ?>">Application</a>
            <a href="applicationedit/<?php echo $idSpace ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="staining/<?php echo $idSpace ?>">Marquage</a>
            <a href="stainingedit/<?php echo $idSpace ?>/0">+</a> 
        </div>
    </div>
    
</div>		
