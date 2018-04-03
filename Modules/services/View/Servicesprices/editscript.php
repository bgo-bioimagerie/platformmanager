<?php ?>

<script>
    $(document).ready(function () {

        $("#hider").hide();
        $("#entriesbuttonclose").click(function () {
            $("#hider").hide();
            $('#entriespopup_box').hide();
        });

<?php for ($i = 0; $i < count($services); $i++) { ?>
            $("#editentry_<?php echo $services[$i]["id"] ?>").click(function () {
                
                var strid = this.id;
                var arrayid = strid.split("_");
                //alert("edit note clicked " + arrayid[1]);
                showEditEntryForm(<?php echo $id_space ?>, arrayid[1]);
            });
    <?php
}
?>

        function showEditEntryForm(id_space, id_service) {
            $.post(
                    'servicesgetprices/' + id_space + '/' + id_service,
                    {},
                    function (data) {
                        $('#service_id').val(data.id_service);
                        $('#service').val(data.service);
                        <?php 
                        foreach($belongings as $belonging){
                            ?>
                             $('<?php echo '#bel_' . $belonging['id'] ?>').val(data.<?php echo 'bel_' . $belonging['id'] ?>);               
                        <?php                    
                        }
                        ?>

                        $("#hider").fadeIn("slow");
                        $('#entriespopup_box').fadeIn("slow");
                    },
                    'json'
                    );

        }
        ;

    });
</script>            