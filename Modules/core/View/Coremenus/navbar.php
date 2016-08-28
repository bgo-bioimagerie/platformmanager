<?php
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$sygrrifmenucolor = $modelCoreConfig->getParam("coremenumenucolor");
$sygrrifmenucolortxt = $modelCoreConfig->getParam("coremenumenucolortxt");
if ($sygrrifmenucolor == "") {
    $sygrrifmenucolor = "337ab7";
}
if ($sygrrifmenucolortxt == "") {
    $sygrrifmenucolortxt = "ffffff";
}
?>

<head>
    <link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">

    <script type="text/javascript" src="externals/datepicker/js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
    <script type="text/javascript" src="externals/datepicker/js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>

    <style>
        .bs-docs-header {
            position: relative;
            color: #<?php echo $sygrrifmenucolortxt ?>;
            text-shadow: 0 0px 0 rgba(0, 0, 0, .1);
            background-color: #<?php echo $sygrrifmenucolor ?>;
            border:0px solid #<?php echo $sygrrifmenucolor ?>;
        }

        #navlink {
            color: #<?php echo $sygrrifmenucolortxt ?>;
            text-shadow: 0 0px 0 rgba(0, 0, 0, .1);
            border:0px solid #<?php echo $sygrrifmenucolor ?>;
        }

        #well {
            margin-top:10px;
            margin-bottom:25px;
            color: #<?php echo $sygrrifmenucolortxt ?>;
            background-color: #<?php echo $sygrrifmenucolor ?>;
            border:0px solid #<?php echo $sygrrifmenucolor ?>;
            -moz-box-shadow: 0px 0px px #000000;
            -webkit-box-shadow: 0px 0px px #000000;
            -o-box-shadow: 0px 0px 0px #000000;
            box-shadow: 0px 0px 0px #000000;
        }

        legend {
            color: #<?php echo $sygrrifmenucolortxt ?>;
        }

        #content{
            margin-top: -15px;
        }

    </style>

</head>

<?php
require_once 'Modules/core/Model/CoreTranslator.php';
?>

<div class="bs-docs-header" id="content" style="padding-top:7px;">
    <div class="container">

          <div class="col-md-4">
                <button onclick="location.href = 'coremenus/'" class="btn btn-link" id="navlink"><?php echo CoreTranslator::Menus($lang) ?></button>
            <br/>
                <button onclick="location.href = 'coremenusitems'" class="btn btn-link" id="navlink"><?php echo CoreTranslator::Items($lang) ?> </button>
                <button onclick="location.href = 'coremenusitemedit/0'" class="btn btn-link" id="navlink">+</button>
           
        </div>
              
    </div>
</div>
