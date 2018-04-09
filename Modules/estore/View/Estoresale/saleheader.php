<div class="col-md-12 text-center pm-form-short" style="margin-top: -7px; padding-top: 12px;">
    
    <div class="col-md-10 text-center">
    <div class="col-md-1" style="height: 50px; margin-top: 12px;">
        <a href="essaleenteredadminedit/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 12px; <?php if ( $salestatus >= 0 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>" ><?php echo EstoreTranslator::Entered($lang) ?></a>
    </div>  
    <div class="col-md-1" style="height: 50px; <?php if ( $salestatus >= 1 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">  
        <span style="font-size: 50px;" class="glyphicon glyphicon-menu-right"></span>
    </div>

    <div class="col-md-1" style="height: 50px; margin-top: 12px;">
        <a href="essalefeasibility/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 12px; <?php if ( $salestatus >= 1 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>" ><?php echo EstoreTranslator::Feasibility($lang) ?></a>
    </div>  
    <div class="col-md-1" style="height: 50px; <?php if ( $salestatus >= 2 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">  
        <span style="font-size: 50px;" class="glyphicon glyphicon-menu-right"></span>
    </div>
    
    <div class="col-md-1" style="height: 50px; margin-top: 12px;">
        <a href="essaletodoquote/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 12px; <?php if ( $salestatus >= 2 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>" ><?php echo EstoreTranslator::TodoQuote($lang) ?></a>
    </div>  
    <div class="col-md-1" style="height: 50px; <?php if ( $salestatus >= 3 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">  
        <span style="font-size: 50px;" class="glyphicon glyphicon-menu-right"></span>
    </div>
    
    <div class="col-md-1" style="height: 50px; margin-top: 12px;">
        <a href="essalequotesent/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 12px; <?php if ( $salestatus >= 3 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>" ><?php echo EstoreTranslator::QuoteSent($lang) ?></a>
    </div>  
    <div class="col-md-1" style="height: 50px; <?php if ( $salestatus >= 4 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">  
        <span style="font-size: 50px;" class="glyphicon glyphicon-menu-right"></span>
    </div>
    
    <div class="col-md-1" style="height: 50px; margin-top: 12px;">
        <a href="essaletosendsale/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 12px; <?php if ( $salestatus >= 4 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>" ><?php echo EstoreTranslator::ToSendSale($lang) ?></a>
    </div>  
    <div class="col-md-1" style="height: 50px; <?php if ( $salestatus >= 5 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">  
        <span style="font-size: 50px;" class="glyphicon glyphicon-menu-right"></span>
    </div>   
    
    <div class="col-md-1" style="height: 50px; margin-top: 12px;">
        <a href="essaleinvoicing/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 12px; <?php if ( $salestatus >= 5 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>" ><?php echo EstoreTranslator::Invoicing($lang) ?></a>
    </div>  
    <div class="col-md-1" style="height: 50px; <?php if ( $salestatus >= 6 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">  
        <span style="font-size: 50px;" class="glyphicon glyphicon-menu-right"></span>
    </div> 
        
    </div>
    <div class="col-md-2 text-center">
    
    <div class="col-md-4 text-center" style="height: 50px; margin-top: 12px;">
        <a href="essalepaymentpending/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 12px; <?php if ( $salestatus >= 6 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>" ><?php echo EstoreTranslator::PaymentPending($lang) ?></a>
    </div>  
    <div class="col-md-4" style="height: 50px; <?php if ( $salestatus >= 7 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>">  
        <span style="font-size: 50px;" class="glyphicon glyphicon-menu-right"></span>
    </div> 
    
    <div class="col-md-4" style="height: 50px; margin-top: 12px;">
        <a href="essaleended/<?php echo $id_space ?>/<?php echo $id_sale ?>" style="font-size: 12px; <?php if ( $salestatus >= 7 ){echo "color: #428bca";}else{echo "color: #e1e1e1";} ?>" ><?php echo EstoreTranslator::Ended($lang) ?></a>
    </div>      
    </div>
    
</div>
