<div class="col-md-12 col-xs-12" style="text-align: center; padding-bottom: 14px; border-bottom: 2px solid #e1e1e1;">

    <div class="btn-group" role="group" aria-label="...">
        <button type="button" <?php
        if ($menuCode[0] == 0) {
            echo "onclick=\"location.href = 'databaseconfiginfo/" . $id_space . "/" . $id_database . "'\"";
        }
        ?> class="btn btn-default <?php
                if ($menuCode[0] == 1) {
                    echo "active";
                }
        ?>"><?php echo DatabaseTranslator::Info($lang) ?></button>
        <button type="button" <?php
                if ($menuCode[1] == 0) {
                    echo "onclick=\"location.href = 'databaseconfigclasses/" . $id_space . "/" . $id_database . "'\"";
                }
        ?> class="btn btn-default <?php
                if ($menuCode[1] == 1) {
                    echo "active";
                }
                ?>"><?php echo DatabaseTranslator::Classes($lang) ?></button>
        <button type="button" <?php
                if ($menuCode[2] == 0) {
                    echo "onclick=\"location.href = 'databaseconfigviews/" . $id_space . "/" . $id_database . "/0'\"";
                }
                ?> class="btn btn-default <?php
    if ($menuCode[2] == 1) {
        echo "active";
    }
    ?>"><?php echo DatabaseTranslator::Views($lang) ?></button>
    
        
        
        <button type="button" <?php
    if ($menuCode[3] == 0) {
        echo "onclick=\"location.href = 'databaseconfigmenu/" . $id_space . "/" . $id_database . "'\"";
    }
    ?> class="btn btn-default <?php
    if ($menuCode[3] == 1) {
        echo "active";
    }
    ?>"><?php echo DatabaseTranslator::Menu($lang) ?></button>
        
    
    <button type="button" <?php
    if ($menuCode[4] == 0) {
        echo "onclick=\"location.href = 'databaseconfigtranslate/" . $id_space . "/" . $id_database . "'\"";
    }
    ?> class="btn btn-default <?php
    if ($menuCode[4] == 1) {
        echo "active";
    }
    ?>"><?php echo DatabaseTranslator::Dictionnary($lang) ?></button>
        
        
    <button type="button" <?php
    if ($menuCode[5] == 0) {
        echo "onclick=\"location.href = 'databaseconfiginstall/" . $id_space . "/" . $id_database . "'\"";
    }
    ?> class="btn btn-default <?php
    if ($menuCode[5] == 1) {
        echo "active";
    }
    ?>"><?php echo DatabaseTranslator::Install($lang) ?></button>  
        
        
    <button type="button" <?php
    if ($menuCode[6] == 0) {
        //echo "onClick=\"window.open(databaseconfigpreview/" . $id_space . "/" . $id_database ." , '_blank')\"";
        echo "onclick=\"location.href = 'databaseconfigpreview/" . $id_space . "/" . $id_database . "'\"";
    }
    ?> class="btn btn-default <?php
    if ($menuCode[6] == 1) {
        echo "active";
    }
    ?>"><?php echo DatabaseTranslator::Preview($lang) ?></button>    
        
    </div>

</div>
