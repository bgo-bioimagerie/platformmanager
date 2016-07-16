<?php
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$sygrrifmenucolor = $modelCoreConfig->getParam("sygrrifmenucolor");
$sygrrifmenucolortxt = $modelCoreConfig->getParam("sygrrifmenucolortxt");
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
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/core/Model/CoreTranslator.php';
?>

<div class="bs-docs-header" id="content" style="padding-top:7px;">
    <div class="container">

        <form role="form" class="form-horizontal" action="booking" method="post" id="navform">
            <div class='col-md-3' id="well">
                <fieldset>
                    <legend><?php echo EcosystemTranslator::Site($lang) ?></legend>
                    <div >
                        <select class="form-control" name="id_site" onchange="getareaval(this);">
                            <?php
                            foreach ($menuData['sites'] as $site) {
                                $siteID = $this->clean($site['id']);
                                $curentPricingId = $this->clean($menuData['curentSiteId']);
                                $selected = "";
                                if ($curentPricingId == $siteID) {
                                    $selected = "selected=\"selected\"";
                                }
                                ?>
                                <option value="<?php echo $siteID ?>" <?php echo $selected ?>> <?php echo $this->clean($site['name']) ?> </option>
                                <?php
                            }
                            ?>
                        </select>
                        <script type="text/javascript">
                            function getareaval(sel) {
                                $("#navform").submit();
                            }
                        </script>
                    </div>
                </fieldset>
            </div>
            <div class='col-md-3' id="well">
                <fieldset>
                    <legend><?php echo ResourcesTranslator::Area($lang) ?></legend>
                    <div >
                        <select class="form-control" name="id_area" onchange="getareaval(this);">
                            <?php
                            foreach ($menuData['areas'] as $area) {
                                $areaID = $this->clean($area['id']);
                                $curentPricingId = $this->clean($menuData['curentAreaId']);
                                $selected = "";
                                if ($curentPricingId == $areaID) {
                                    $selected = "selected=\"selected\"";
                                }
                                ?>
                                <option value="<?php echo $areaID ?>" <?php echo $selected ?>> <?php echo $this->clean($area['name']) ?> </option>
                                <?php
                            }
                            ?>
                        </select>
                        <script type="text/javascript">
                            function getareaval(sel) {
                                $("#navform").submit();
                            }
                        </script>
                    </div>
                </fieldset>
            </div>
            <div class='col-md-3' id="well">
                <fieldset>
                    <legend><?php echo ResourcesTranslator::Resource($lang) ?></legend>
                    <div >
                        <select class="form-control" name="id_resource"  onchange="getresourceval(this);">
                            <option value="0" > ... </option>
                            <?php
                            foreach ($menuData['resources'] as $resource) {
                                $resourceID = $this->clean($resource['id']);
                                $curentResourceId = $this->clean($menuData['curentResourceId']);
                                $selected = "";
                                if ($curentResourceId == $resourceID) {
                                    $selected = "selected=\"selected\"";
                                }
                                ?>
                                <option value="<?php echo $resourceID ?>" <?php echo $selected ?>> <?php echo $this->clean($resource['name']) ?> </option>
                                <?php
                            }
                            ?>
                        </select>
                        <script type="text/javascript">
                            function getresourceval(sel) {
                                $("#navform").submit();
                            }
                        </script>
                    </div>
                </fieldset>
            </div>
            <div class='col-md-2' id="well">
                <fieldset>
                    <legend><?php echo CoreTranslator::Date($lang) ?></legend>
                    <div >
                        <div class='input-group date form_date_<?php echo $lang ?>'>
                            <input id="date-daily" type='text' class="form-control" name="curentDate"
                                   value="<?php echo CoreTranslator::dateFromEn($menuData["curentDate"], $lang) ?>"
                                   />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class='col-md-1' id="well">
                <fieldset>
                    <legend style="color:#<?php echo $sygrrifmenucolor ?>; border:0px solid #<?php echo $sygrrifmenucolor ?>;">.</legend>
                    <div >
                        <input type="submit" class="btn btn-primary" value="ok" />
                    </div>
                </fieldset>
            </div>   
        </form>
    </div>
</div>

<?php include "Framework/timepicker_script.php" ?>
