<?php ?>

<script>
    $(document).ready(function () {

        $("#hider").hide();
        $("#entriesbuttonclose").click(function () {
            $("#hider").hide();
            $('#entriespopup_box').hide();
        });

<?php for ($i = 0; $i < count($resources); $i++) { ?>
            $("#editentry_<?php echo $resources[$i]["id"] ?>").click(function () {
                
                var strid = this.id;
                var arrayid = strid.split("_");
                //alert("add note clicked " + arrayid[1]);
                showEditEntryForm(<?php echo $id_space ?>, arrayid[1]);
            });
    <?php
}
?>

        function showEditEntryForm(id_space, id_service) {
            $.post(
                    'bookinggetprices/' + id_space + '/' + id_service,
                    {},
                    function (data) {
                        $('#resource_id').val(data.id_resource);
                        $('#resource').val(data.resource);
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