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
            <a id="menu-button" href="anticorps/<?php echo $id_space ?>">Anticorps</a>
            <a href="anticorpsedit/<?php echo $id_space ?>/0">+</a> 
        </div>    
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="protocols/<?php echo $id_space ?>/id">Protocoles</a>
            <a href="protocolsedit/<?php echo $id_space ?>/0">+</a>
        </div>
    </div>

    <div class="col-12">
        <p id="separatorp">Référence</p>

        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="sources/<?php echo $id_space ?>">Sources</a>
            <a href="sourcesedit/<?php echo $id_space ?>/0">+</a> 
        </div>    
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="isotypes/<?php echo $id_space ?>">Isotypes</a>
            <a href="isotypesedit/<?php echo $id_space ?>/0">+</a>
        </div>
    </div>
    
        <div class="col-12">
        <p id="separatorp">Tissus</p>

        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="especes/<?php echo $id_space ?>">Espèces</a>
            <a href="especesedit/<?php echo $id_space ?>/0">+</a> 
        </div>    
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="organes/<?php echo $id_space ?>">Organes</a>
            <a href="organesedit/<?php echo $id_space ?>/0">+</a>
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="prelevements/<?php echo $id_space ?>">Prélèvements</a>
            <a href="prelevementsedit/<?php echo $id_space ?>/0">+</a>
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="status/<?php echo $id_space ?>">Status</a>
            <a href="statusedit/<?php echo $id_space ?>/0">+</a>
        </div>
    </div>

    <div class="col-12">
        <p id="separatorp">Détails Proto</p>

        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="kit/<?php echo $id_space ?>">KIT</a>
            <a href="kitedit/<?php echo $id_space ?>/0">+</a> 
        </div>    
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="proto/<?php echo $id_space ?>">Proto</a>
            <a href="protoedit/<?php echo $id_space ?>/0">+</a> 
        </div>    
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="fixative/<?php echo $id_space ?>">Fixative</a>
            <a href="fixativeedit/<?php echo $id_space ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="option/<?php echo $id_space ?>">Option</a>
            <a href="optionedit/<?php echo $id_space ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="enzymes/<?php echo $id_space ?>">Enzyme</a>
            <a href="enzymesedit/<?php echo $id_space ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="dem/<?php echo $id_space ?>">Dém</a>
            <a href="demedit/<?php echo $id_space ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="aciinc/<?php echo $id_space ?>">AcI Inc</a>
            <a href="aciincedit/<?php echo $id_space ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="linker/<?php echo $id_space ?>">Linker</a>
            <a href="linkeredit/<?php echo $id_space ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="inc/<?php echo $id_space ?>">Linker Inc</a>
            <a href="incedit/<?php echo $id_space ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="acii/<?php echo $id_space ?>">acII</a>
            <a href="aciiedit/<?php echo $id_space ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="application/<?php echo $id_space ?>">Application</a>
            <a href="applicationedit/<?php echo $id_space ?>/0">+</a> 
        </div>
        
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="staining/<?php echo $id_space ?>">Marquage</a>
            <a href="stainingedit/<?php echo $id_space ?>/0">+</a> 
        </div>
    </div>
    
</div>        
