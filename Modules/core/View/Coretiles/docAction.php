<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('stylesheet') ?>

<link rel="stylesheet" type="text/css" href="externals/bootstrap/css/bootstrap.min.css">
<?php
$headless = Configuration::get("headless");
if (!$headless) {
    ?>
    <link href="externals/core/theme/navbar-fixed-top.css" rel="stylesheet">
    <?php
}
?>
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/core.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/space.css' />

<?php endblock(); ?>
<!-- body -->     
<?php startblock('content') ?>

<div class="col-xs-12 pm-tile-container">

    <div class="container">
        <h3> Configuration help </h3>
        <p> This page is displayed because you have no navigation items configured.  </p>
        <p> Navigation items allows to store and display spaces using a 2 level tree. Here is the procedure 
        to configure items:
        <ul>
            <li> Create root navigation items from "administration" > "Menu". These items 
                will be displayed in top main navigation bar.  
            </li>
            <li>
                Create navigation sub-items from "administration" > "Menu" > "sub menu". Each sub-item is related
                to a root navigation item.
            </li>
            <li>
                Each sub-navigation item can contain as many spaces as you need. First create spaces from
                "administration" > "spaces", and then you can create and associate leaf items to each sub-items
                from "administration" > "Menu" > "items"
            </li>
            
        </ul>
        </p>
        
    </div>

</div>
<?php
endblock();
