<?php
require_once 'Modules/core/Model/CoreSpace.php';
$modelMenu = new CoreSpace();

$menucolor = $modelMenu->getSpaceMenusColor($id_space, "booking");
$menucolortxt = $modelMenu->getSpaceMenusTxtColor($id_space, "booking");
if ($menucolor == "") {
    $menucolor = "#428bca";
}
if ($menucolortxt == "") {
    $menucolortxt = "#ffffff";
}
?>

    <style>
        .bs-docs-header {
            position: relative;
            color: <?php echo $menucolortxt ?>;
            text-shadow: 0 0px 0 rgba(0, 0, 0, .1);
            background-color: <?php echo $menucolor ?>;
            border:0px solid <?php echo $menucolor ?>;
        }

        #navlink {
            color: <?php echo $menucolortxt ?>;
            text-shadow: 0 0px 0 rgba(0, 0, 0, .1);
            border:0px solid <?php echo $menucolor ?>;
        }

        #well {
            margin-top:10px;
            padding-bottom:25px;
            color: <?php echo $menucolortxt ?>;
            background-color: <?php echo $menucolor ?>;
            border:0px solid <?php echo $menucolor ?>;
            -moz-box-shadow: 0px 0px px #000000;
            -webkit-box-shadow: 0px 0px px #000000;
            -o-box-shadow: 0px 0px 0px #000000;
            box-shadow: 0px 0px 0px #000000;
        }

        legend {
            color: <?php echo $menucolortxt ?>;
        }



    </style>


<?php
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/core/Model/CoreTranslator.php';
?>

    <div class="bs-docs-header" id="">
        
            <form role="form" class="form-horizontal" action="booking/<?php echo $id_space ?>" method="post" id="navform">

                <div class='col-md-4' id="well">
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
                <div class='col-md-4' id="well">
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
                <div class='col-md-3' id="well">
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
                        <legend style="color:<?php echo $menucolor ?>;">.</legend>
                        <div >
                            <input type="submit" class="btn btn-default" value="ok" />
                        </div>
                    </fieldset>
                </div>   
            </form>
    </div>
    

<?php include "Framework/timepicker_script.php" ?>
