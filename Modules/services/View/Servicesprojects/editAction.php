<?php include 'Modules/services/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="pm-form">

    <div class="col-md-12">
        <?php
        if (isset($_SESSION["message"]) && $_SESSION["message"]) {
            ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION["message"] ?>
            </div>
            <?php
            unset($_SESSION["message"]);
        }
        ?>
    </div>

<?php echo $formHtml ?>
<script type="module">
    import {DynamicForms} from '/externals/pfm/dynamics/dynamicForms.js';
    let dynamicForms = new DynamicForms();
    let spaceId = <?php echo $id_space?>;
    let sourceId = "id_user";
    let targets = [
        {
            elementId: "id_client",
            apiRoute: `clientusers/getclients/`,
            activateOnLoad: true
        }
    ];
    dynamicForms.dynamicFields(sourceId, targets, spaceId);
</script>
</div>

<?php
endblock();
