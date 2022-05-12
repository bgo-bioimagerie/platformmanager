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

        $("#addtissusbutton").click(function () {
            //alert("add tissus clicked ");
            $('#id').val(0);
            $('#id_antibody').val(<?php echo $id ?>);
            $('#ref_protocol').val(0);
            $('#dilution').val("");
            $('#comment').val("");
            $('#espece').val(0);
            $('#organe').val(0);
            $('#status').val(0);
            $('#ref_bloc').val("");
            $('#prelevement').val(0);

            $("#hider").fadeIn("slow");
            $('#tissuspopup_box').fadeIn("slow");
        });

        $("#addownerbutton").click(function () {
            $('#owner_id').val(0);
            $('#owner_id_anticorps').val(<?php echo $id ?>);
            $('#owner_id_user').val("");

            $('#owner_disponible').val("");
            $('#owner_date_recept').val("");
            $('#owner_no_dossier').val("");

            $("#hider").fadeIn("slow");
            $('#ownerpopup_box').fadeIn("slow");
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

<?php for ($i = 0; $i < count($owners); $i++) { ?>
            $("#editowner_<?php echo $owners[$i]["id"] ?>").click(function () {

                var strid = this.id;
                var arrayid = strid.split("_");
                //alert("add note clicked " + arrayid[1]);
                showEditOwnerForm(<?php echo $id_space ?>, arrayid[1]);
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
                        $('#id_antibody').val(data.id_anticorps);
                        $('#ref_protocol').val(data.ref_protocol);
                        $('#dilution').val(data.dilution);
                        $('#comment').val(data.comment);
                        $('#espece').val(data.espece);
                        $('#organe').val(data.organe);
                        $('#status').val(data.status);
                        $('#ref_bloc').val(data.ref_bloc);
                        $('#prelevement').val(data.prelevement);

                        $("#hider").fadeIn("slow");
                        $('#tissuspopup_box').fadeIn("slow");
                    },
                    'json'
                    );

        }
        ;

        function showEditOwnerForm(id_space, id_owner) {
            $.post(
                    'apiantibodyowner/' + id_space + '/' + id_owner,
                    {},
                    function (data) {
                        $('#owner_id').val(data.id);
                        $('#owner_id_anticorps').val(data.id_anticorps);
                        $('#owner_id_user').val(data.id_utilisateur);

                        $('#owner_disponible').val(data.disponible);
                        $('#owner_date_recept').val(data.date_recept);
                        $('#owner_no_dossier').val(data.no_dossier);

                        $("#hider").fadeIn("slow");
                        $('#ownerpopup_box').fadeIn("slow");
                    },
                    'json'
                    );
        }
        ;

    });
</script>            