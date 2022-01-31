<?php
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Framework/Constants.php';

$modelMenu = new CoreSpace();

$menucolor = $modelMenu->getSpaceMenusColor($id_space, "booking");
$menucolortxt = $modelMenu->getSpaceMenusTxtColor($id_space, "booking");
if ($menucolor == "") {
    $menucolor = "#428bca";
}
if ($menucolortxt == "") {
    $menucolortxt = Constants::COLOR_WHITE;
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
require_once 'Modules/booking/Model/BookingTranslator.php';
?>

    <div class="bs-docs-header" id="">
        
            <form role="form" class="form-horizontal" action="booking/<?php echo $id_space ?>" method="post" id="navform">

                <div class='col-md-2' id="well">
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
                <?php
                $dateSize = 6;
                if(isset($users) && count($users) > 1) {
                    $dateSize = 3;
                }
                ?>


                <div class='col-md-<?php echo $dateSize; ?>' id="well">
                    <fieldset>
                        <legend><?php echo CoreTranslator::Date($lang) ?></legend>
                        <div >
                            <div class='input-group date '>
                                <input id="date-daily" type='date' class="form-control" name="curentDate"
                                       value="<?php echo $menuData["curentDate"] ?>"
                                       />
                            </div>
                        </div>
                    </fieldset>
                </div>
                <?php if(isset($users) && count($users) > 1) {  ?>
                <div class='col-md-3' id="well">
                    <fieldset>
                        <legend><?php echo CoreTranslator::User($lang) ?></legend>
                        <div>
                            <div class='input-group'>
                                <?php
                                $curUser = "all:0";
                                if(!isset($id_user)){
                                    $id_user = '';
                                }
                                if($id_user){
                                    for($i=0;$i<count($users);$i++){
                                        if($users[$i]['id'] == $id_user) {
                                            $curUser = ($users[$i]['firstname'] ? $users[$i]['name'].' '.$users[$i]['firstname'] : $users[$i]['name']).':'.$users[$i]['id'];
                                        }
                                    }
                                }
                                ?>

                                <?php
                                if($context['role']<CoreSpace::$MANAGER) { ?>
                                    <select class="form-control" id="id_user" name="id_user" onchange="$('#navform').submit();">
                                        <option value="0"><?php echo BookingTranslator::ShowAll($lang); ?></option>
                                        <option <?php if($id_user) { echo "selected";} ?> value="<?php echo $users[1]['id'] ?>"><?php echo BookingTranslator::ShowMine($lang); ?></option>
                                    </select>
                                <?php } else {
                                ?>
                                <input type="hidden" id="id_user" name="id_user" value="<?php echo $id_user ?>"/>
                                <input class="form-control" list="user_list" value="<?php echo $curUser?>" onchange="getuserval(this.value)"/>

                                <datalist id="user_list">
                                <?php
                                    foreach($users as $i => $user){
                                        $selected = "";
                                        if($i == 0 && !$id_user) {
                                            $selected = 'selected';
                                        }
                                        if($id_user == $user['id']) {
                                            $selected = 'selected';
                                        }
                                    ?>
                                    <option <?php echo $selected ?> value="<?php echo ($user['firstname'] ? $user['name'].' '.$user['firstname'] : $user['name']).':'.$user['id'] ?>">
                                    <?php } ?>                                    
                                </datalist>
                                <script type="text/javascript">
                                function getuserval(sel) {
                                    let user = sel.split(':')
                                    let id_user = document.getElementById('id_user');
                                    id_user.value = user[1];
                                    $("#navform").submit();
                                }
                                </script>
                                <?php } ?>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <?php } ?>
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
