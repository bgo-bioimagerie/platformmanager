<?php ?>

<script>
    $(document).ready(function () {

        $("#hider").hide();
        $("#tissusbuttonclose").click(function () {
            $("#hider").hide();
            $('#tissuspopup_box').hide();
        });
        $("#ownerbuttonclose").click(function () {
            $("#hider").hide();
            $('#ownerpopup_box').hide();
        });

<?php for ($i = 0; $i < count($tissus); $i++) { ?>
            $("#edittissus_<?php echo $tissus[$i]["id"] ?>").click(function () {
                
                var strid = this.id;
                var arrayid = strid.split("_");
                //alert("add note clicked " + arrayid[1]);
                showEditTissusForm(<?php echo $id_space ?>, arrayid[1]);
            });
    <?php
}
?>

        function showEditTissusForm(id_space, id_tissus) {
            $.post(
                    'apiantibodytissus/' + id_space + '/' + id_tissus,
                    {},
                    function (data) {
                        $('#id').val(data.id);
                        $('#ref_protocol').val(data.ref_protocol);
                        $('#dilution').val(data.dilution);
                        $('#comment').val(data.comment);
                        $('#espece').val(data.espece);
                        $('#status').val(data.status);
                        $('#ref_bloc').val(data.ref_bloc);
                        $('#prelevement').val(data.prelevement);
                        $('#image_url').val(data.image_url);
                        
                        $("#hider").fadeIn("slow");
                        $('#tissuspopup_box').fadeIn("slow");
                    },
                    'json'
                    );

        }
        ;

    });
</script>            