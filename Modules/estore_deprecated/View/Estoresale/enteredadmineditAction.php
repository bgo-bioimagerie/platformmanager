<?php include 'Modules/estore/View/layoutsale.php' ?>

<!-- body -->     
<?php startblock('content') ?>

    <div class="col-md-12 pm-form">
        
        <h3><?php echo EstoreTranslator::EnteredSale($lang) ?></h3>
        <h4><?php echo EstoreTranslator::Informations($lang) ?></h4>
        <p>
            La demande #<?php echo $saleInfo["id"] ?> à été entré par <?php echo $history["username"] ?> le <?php echo $history["date"] ?>
        </p>
        <ul>
            <li><b><?php echo EstoreTranslator::ClientAccount($lang) ?> :</b> <?php echo $saleInfo["client"] ?> </li>
            <li><b><?php echo EstoreTranslator::DateExpected($lang) ?> :</b> <?php echo $saleInfo["date_expected"] ?> </li>
            <li><b><?php echo EstoreTranslator::ContactType($lang) ?> :</b> <?php echo $saleInfo["contacttype"] ?> </li>
            <li><b><?php echo EstoreTranslator::FurtherInformation($lang) ?> :</b> <?php echo $saleInfo["further_information"] ?> </li>
        </ul>
        
        <h4>Détails</h4>
        <?php echo $tableHtml ?>
        
        <div class="col-md-12 text-right">
            <a class="btn btn-primary" href="essalefeasibility/<?php echo $id_space ?>/<?php echo $saleInfo["id"] ?>">Faisabilité</a>
        </div>    
    </div>
<?php
endblock();

