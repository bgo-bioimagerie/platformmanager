<?php $this->title = "Catalog" ?>

<?php require_once 'Modules/catalog/Model/CatalogTranslator.php'; ?>

<style>

    a.mybuttonactive {
        /* display et dimensions */
        display: inline-block;
        width: 200px;
        height: 50px;
        /* centrage vertical */
        line-height: 50px;
        vertical-align: middle;
        /* centrage horizontal */
        text-align: center;
        /* font style */
        font-family: Arial,sans-serif;
        font-size: medium; 
        color: white;
        text-decoration: none;
        font-weight: bold;
        /* background style */
        background: #337ab7;
    }

    a.mybutton {
        /* display et dimensions */
        display: inline-block;
        width: 200px;
        height: 50px;
        /* centrage vertical */
        line-height: 50px;
        vertical-align: middle;
        /* centrage horizontal */
        text-align: center;
        /* font style */
        font-family: Arial,sans-serif;
        font-size: medium; 
        color: #337ab7;
        text-decoration: none;
        font-weight: bold;
        /* background style */
        background: #ffffff;
        border: 1px solid #337ab7;
    }

    a.mybutton:hover { background: #337ab7; color: #ffffff; border: 1px solid #337ab7;}

</style>
<div class="col-12" style="background-color:#ffffff;">
    <div class="col-12">
        <div class="col-6">
            <h1> <?php echo $pageTitle ?></h1>
        </div>
        <div class="col-6 text-right" style="margin-top:12px;">
            <img alt="logo" src="<?php echo $pageLogo ?>" height="50px" />
        </div>
    </div>
    <div class="page-header">
        <br/>
    </div>
    <br/>
    <div class="col-12" style="text-align:center; background-color:#ffffff;">
        <?php
        foreach ($categories as $cat) {
            $buttonStyle = "mybutton";
            if ($cat["id"] == $activeCategory) {
                $buttonStyle = "mybuttonactive";
            }
            ?>
            <a class="<?php echo $buttonStyle ?>" href="catalogpublic/<?php echo $id_space . "/" . $cat["id"] ?>"><?php echo $cat["name"] ?></a>
            <?php
        }
?>

    </div>
</div>