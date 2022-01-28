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
<div class="row" style="background-color:#ffffff;">
    <div class="col-xs-12" style="text-align: center;">
        <h2> <?php echo CatalogTranslator::Catalog($lang) ?></h2>
    </div>
    <br/>
    <?php foreach($categories as $cat) {
        $selectedStyle = "";
        if ($cat["id"] == $activeCategory) {
            $selectedStyle = "background-color: #337ab7; color: #ffffff";
        }
    ?>
        <div class="col-xs-4 col-md-2">
            <div class="card" style="text-align: center; <?php echo $selectedStyle; ?>">
                <div class="card-header"></div>
                <div class="card-body">
                    <a style="text-align: center; <?php echo $selectedStyle; ?>" href="catalog/<?php echo $id_space . "/" . $cat["id"] ?>"><?php echo $cat["name"] ?></a>
                </div>
            </div>
        </div>
    <?php } ?>
</div>