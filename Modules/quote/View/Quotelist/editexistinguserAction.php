<?php include 'Modules/quote/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<?php
if ($id_quote > 0) {
    ?>
    <div class="col-md-12 pm-form-short">
        <?php
    } else {
        ?>
        <div class="col-md-12 pm-form">
            <?php
        }
        ?>
        <?php
        if (isset($_SESSION["message"])) {
            ?>
            <div class="alert alert-success">
                <?php echo $_SESSION["message"] ?>
            </div>
            <?php
            unset($_SESSION["message"]);
        }
        ?>
        <h3><?php echo QuoteTranslator::EditQuote($lang) ?></h3>
        <?php echo $formHtml ?>
        <script type="text/javascript" src="/Framework/utilities/dynamicSelector.js"></script>
    </div>

    <?php
    if ($tableHtml != "") {
        ?>
        <div class="col-md-12 pm-form">
            <br/>
            <a class="btn btn-primary" id="additembutton"><?php echo QuoteTranslator::NewItem($lang) ?></a>
            <a class="btn btn-danger" href="quotepdf/<?php echo $id_space.'/'.$id_quote ?>"><?php echo QuoteTranslator::PDF($lang) ?></a>

            <?php echo $tableHtml ?>
            
        </div>
        <?php
    }
    ?>

    <!--  *************  -->
    <!--  Popup windows  -->
    <!--  *************  -->
    <link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">
    <div id="hider" class="col-xs-12"></div> 
    <div id="itemspopup_box" class="pm_popup_box" style="display: none;">
        <div class="col-md-1 col-md-offset-11" style="text-align: right;"><a id="itemsbuttonclose" class="glyphicon glyphicon-remove" style="cursor:pointer;"></a></div>
            <?php echo $formitemHtml ?>
    </div> 


    <?php include 'Modules/quote/View/Quotelist/editnewscript.php'; ?>

    <?php
    endblock();
    