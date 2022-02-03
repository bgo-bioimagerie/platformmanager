<?php ?>

<script>

    function editentry(id) {
        var arrayid = id.split("_");
        showEditEntryForm(<?php echo $id_space ?>, arrayid[1]);
    }

    function showEditEntryForm(id_space, id) {
        $.post(
            'servicesgetprojectentry/' + id_space + '/' + id,
            {},
            function (data) {
                console.log('????', data);
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

    $(document).ready(function () {

        $("#hider").hide();
        $("#entriesbuttonclose").click(function () {
            $("#hider").hide();
            $('#entriespopup_box').hide();
        });

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

    });
</script>            