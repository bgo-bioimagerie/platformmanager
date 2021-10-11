<?php ?>

<script>
    $(document).ready(function () {

        $("#hider").hide();
        $("#entriesbuttonclose").click(function () {
            $("#hider").hide();
            $('#entriespopup_box').hide();
        });

<?php for ($i = 0; $i < count($projectEntries); $i++) { ?>
            $("#editentry_<?php echo $projectEntries[$i]["id"] ?>").click(function () {
                
                var strid = this.id;
                var arrayid = strid.split("_");
                //alert("add note clicked " + arrayid[1]);
                showEditEntryForm(arrayid[1], <?php echo $projectEntries[$i]["id_space"]?>);
            });
    <?php
}
?>

        $("#addentrybutton").click(function () {

            $('#formprojectentryprojectid').val(<?php echo $id_project ?>);
            $('#formprojectentrydate').val("");
            $('#formprojectentryid').val(0);
            $('#formserviceid').val(0);
            $('#formservicequantity').val("");
            $('#formservicecomment').val("");

            $("#hider").fadeIn("slow");
            $('#entriespopup_box').fadeIn("slow");
        })
        ;

        function showEditEntryForm(id, id_space) {
            $.post(
                    'servicesgetprojectentry/' + id + '/' + id_space,
                    {},
                    function (data) {
                        $('#formprojectentryprojectid').val(data.id_project);
                        $('#formprojectentrydate').val(data.date);
                        $('#formprojectentryid').val(data.id);
                        $('#formserviceid').val(data.id_service);
                        $('#formservicequantity').val(data.quantity);
                        $('#formservicecomment').val(data.comment);

                        $("#hider").fadeIn("slow");
                        $('#entriespopup_box').fadeIn("slow");
                    },
                    'json'
                    );

        }
        ;

    });
</script>            