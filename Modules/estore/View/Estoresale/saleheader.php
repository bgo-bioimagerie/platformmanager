<div class="col-lg-12 text-center pm-form-short" style="padding-top: 12px;">
    
    <div class="col-md-1 col-md-offset-1" style="height: 50px; margin-top: 12px; <?php if ( $salestatus >= 1 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">
        <a href="essaleenteredadminedit/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 18px;" ><?php echo EstoreTranslator::Entered($lang) ?></a>
    </div>  
    <div class="col-md-1" style="height: 50px; <?php if ( $salestatus >= 1 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">  
        <span style="font-size: 50px;" class="glyphicon glyphicon-menu-right"></span>
    </div>

    <div class="col-md-1" style="height: 50px; margin-top: 0px;<?php if ( $salestatus >= 2 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">
        <a href="essaleinprogress/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 18px; <?php if ( $salestatus >= 2 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>" ><?php echo EstoreTranslator::InProgress($lang) ?></a>
    </div>  
    <div class="col-md-1" style="height: 50px;<?php if ( $salestatus >= 2 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">  
        <span style="font-size: 50px;" class="glyphicon glyphicon-menu-right"></span>
    </div>

    <div class="col-md-1" style="height: 50px; margin-top: 12px;<?php if ( $salestatus >= 3 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">
        <a href="esalequote/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 18px; <?php if ( $salestatus >= 3 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>" ><?php echo EstoreTranslator::Quoted($lang) ?></a>
    </div>  
    <div class="col-md-1" style="height: 50px;<?php if ( $salestatus >= 3 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">  
        <span style="font-size: 50px;" class="glyphicon glyphicon-menu-right"></span>
    </div>

    <div class="col-md-1" style="height: 50px; margin-top: 12px;<?php if ( $salestatus >= 4 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">
        <a href="esaledelivery/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 18px; <?php if ( $salestatus >= 4 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>" ><?php echo EstoreTranslator::Sent($lang) ?></a>
    </div>  
    <div class="col-md-1" style="height: 50px;<?php if ( $salestatus >= 4 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">  
        <span style="font-size: 50px;" class="glyphicon glyphicon-menu-right"></span>
    </div>

    <div class="col-md-1" style="height: 50px; margin-top: 12px;<?php if ( $salestatus >= 5 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">
        <a href="esaleinvoice/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 18px; <?php if ( $salestatus >= 5 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>" ><?php echo EstoreTranslator::Invoiced($lang) ?></a>
    </div>  

</div>
