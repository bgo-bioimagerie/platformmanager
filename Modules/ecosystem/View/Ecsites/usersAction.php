<?php include 'Modules/core/View/layout.php' ?>
 
<script>
        function addRow(tableID) {

            var idx = 1;
            if (tableID == "dataTable") {
                idx = 1;
            }
            var table = document.getElementById(tableID);

            var rowCount = table.rows.length;
            //document.write(rowCount);
            var row = table.insertRow(rowCount);
            //document.write(row);
            var colCount = table.rows[idx].cells.length;
            //document.write(colCount);

            for (var i = 0; i < colCount; i++) {

                var newcell = row.insertCell(i);

                newcell.innerHTML = table.rows[idx].cells[i].innerHTML;
                //alert(newcell.childNodes);
                switch (newcell.childNodes[0].type) {
                    case "date":
                        newcell.childNodes[0].value = "";
                        break;
                    case "text":
                        newcell.childNodes[0].value = "";
                        break;    
                    case "checkbox":
                        newcell.childNodes[0].checked = false;
                        break;
                    case "select-one":
                        newcell.childNodes[0].selectedIndex = 0;
                        break;
                }
            }
        }

        function deleteRow(tableID) {
            try {

                var idx = 2;
                if (tableID == "dataTable") {
                    idx = 2;
                }
                var table = document.getElementById(tableID);
                var rowCount = table.rows.length;

                for (var i = 0; i < rowCount; i++) {
                    var row = table.rows[i];
                    var chkbox = row.cells[0].childNodes[0];
                    if (null != chkbox && true == chkbox.checked) {
                        if (rowCount <= idx) {
                            alert("Cannot delete all the rows.");
                            break;
                        }
                        table.deleteRow(i);
                        rowCount--;
                        i--;
                    }
                }
            } catch (e) {
                alert(e);
            }
        }

    </script>
    

<!-- body -->     
<?php startblock('content') ?>
<div class="container">
    
    <div class="col-lg-12">
        <div class="col-lg-10 col-lg-offset-1">
                <?php if (isset($_SESSION["message"]) && $_SESSION["message"] != ""): 
                    ?>
                    <div class="alert alert-success text-center">	
                   
                <p><?php echo  $message ?></p>
                </div>
                <?php endif; ?>
        </div>
    </div>
    <div class="col-md-12">
	<form role="form" class="form-horizontal" action="coresites/siteusersquery"
		method="post">
	
            <div class="page-header">
                <h1>
                <?php echo EcosystemTranslator::Managers_for_site($lang) . ": " . $siteInfo["name"] ?> 
		<br> <small></small>
                </h1>
            </div>
		
            <input type="hidden" name="id_site" value="<?php echo $siteInfo["id"] ?>"/>
            
            <table id="dataTable" class="table table-striped">
            <thead>
            <tr>
                <th></th>
                <th style="min-width:10em;"><?php echo CoreTranslator::User($lang) ?></th>
                <th style="min-width:10em;"><?php echo CoreTranslator::Status($lang) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (count($admins) >= 1){
            	foreach($admins as $siteAdm){
                ?>
                <tr>
                    <td><input type="checkbox" name="chk"/></td>
                    <td>
                        <select class="form-control" name="id_user[]">
                            <?php
                            $sectorid = $this->clean($siteAdm["id_user"]);
                            foreach ($users as $user) {
                                $ide = $this->clean($user["id"]);
                                $namee = $this->clean($user["name"] . " " . $user["firstname"]);
                                $selected = "";
                                if ($sectorid == $ide) {
                                    $selected = "selected=\"selected\"";
                                }
                                ?>
                                <OPTION value="<?= $ide ?>" <?= $selected ?>> <?= $namee ?> </OPTION>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                    	<select class="form-control" name="id_status[]">
                            <?php
                            $id_status = $this->clean($siteAdm["id_status"]);
                            ?>
                            <OPTION value="3" <?php if($id_status == 3){ echo "selected=\"selected\"";} ?>> <?php echo EcosystemTranslator::Manager($lang) ?> </OPTION>
                            <OPTION value="4" <?php if($id_status == 4){ echo "selected=\"selected\"";} ?>> <?php echo EcosystemTranslator::Admin($lang) ?> </OPTION>
                        
                        </select>
                    </td>
                    
                </tr>
            <?php
            	}
            }
            else{
                ?>
                <tr>
                    <td><input type="checkbox" name="chk"/></td>
                    <td>
                        <select class="form-control" name="id_user[]">
                            <?php
                            foreach ($users as $user) {
                                $ide = $this->clean($user["id"]);
                                $namee = $this->clean($user["name"] . " " . $user["firstname"]);
                                ?>
                                <OPTION value="<?= $ide ?>"> <?= $namee ?> </OPTION>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                    	<select class="form-control" name="id_status[]">
                            
                            <OPTION value="3" > <?php echo EcosystemTranslator::Manager($lang) ?> </OPTION>
                            <OPTION value="4" > <?php echo EcosystemTranslator::Admin($lang) ?> </OPTION>
                        </select>
                    </td>
                    
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <div class="col-md-6">
            <input type="button" class="btn btn-default" value="<?php echo CoreTranslator::Add($lang) ?>"
                   onclick="addRow('dataTable')"/>
            <input type="button" class="btn btn-default" value="<?php echo CoreTranslator::Remove($lang) ?>"
                   onclick="deleteRow('dataTable')"/> <br>
        </div>
            
       <div class="col-xs-2 col-xs-offset-10" id="button-div">
            <input type="submit" class="btn btn-primary" value="<?php echo CoreTranslator::Save($lang) ?>"/>
	</div> 

    </div>
</div>
<?php
endblock();