<?php 
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

$modelCoreConfig = new CoreConfig();
$menucolor = $modelCoreConfig->getParamSpace("invoicesmenucolor", $id_space);
$menucolortxt = $modelCoreConfig->getParamSpace("invoicesmenucolortxt", $id_space);
if ($menucolor == ""){
	$menucolor = "#337ab7";
}
if($menucolortxt == ""){
	$menucolortxt = "#ffffff";
}
?>

<div class="col-md-12" style="padding: 7px; background-color: <?php echo $menucolor ?>; color:<?php echo $menucolortxt ?>;">
    
    <div class="col-md-2" style="margin-top: 0px;">
        <h2><?php echo InvoicesTranslator::invoices($lang) ?></h2>
    </div>
    <div class="col-md-10">
        
        <div class="col-md-3">
            <div class="btn-group" data-toggle="buttons">
                <button onclick="location.href='servicesprices/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::PricesServices($lang) ?></button>
                <button onclick="location.href='bookingprices/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  BookingTranslator::PricesBooking($lang) ?></button>
            </div>
        </div>        
        
        <div class="col-md-3">
            <div class="btn-group" data-toggle="buttons">
            	<button onclick="location.href='servicesinvoiceorder/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::Invoice_order($lang) ?></button>
		<button onclick="location.href='servicesinvoiceproject/<?php echo $id_space ?>/0'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::Invoice_project($lang) ?></button>
            	<button onclick="location.href='bookinginvoice/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  BookingTranslator::Invoice_booking($lang) ?></button>
            </div>
            
        </div>
        
        <div class="col-md-3">
            <div class="btn-group" data-toggle="buttons">
                <button onclick="location.href='invoices/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo InvoicesTranslator::Invoices($lang) ?></button>
            </div>
        </div>
    </div>
</div>
