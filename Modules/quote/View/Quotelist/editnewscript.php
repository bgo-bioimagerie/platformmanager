<?php ?>

<script>
    $(document).ready(function () {

        $("#hider").hide();
        $("#itemsbuttonclose").click(function () {
            $("#hider").hide();
            $('#itemspopup_box').hide();
        });

<?php for ($i = 0; $i < count($items); $i++) { ?>
            $("#edititem_<?php echo $items[$i]["id"] ?>").click(function () {

                var strid = this.id;
                var arrayid = strid.split("_");
                //alert("add note clicked " + arrayid[1]);
                showEditForm(arrayid[1]);
            });
    <?php
}
?>

        $("#additembutton").click(function () {

            $('#id_quote').val(<?php echo $id_quote ?>);
            $('#id').val(0);
            $('#id_item').val(0);
            $('#quantity').val("");
            $('#comment').val("");

            $("#hider").fadeIn("slow");
            $('#itemspopup_box').fadeIn("slow");
        })
                ;

        function showEditForm(id) {
            $.post(
                    'quotegetitem/<?php echo $id_space ?>/' + id,
                    {},
                    function (data) {

                        $('#id_quote').val(<?php echo $id_quote ?>);
                        $('#id').val(data.id);
                        $('#id_item').val(data.module + "_" + data.id_content);
                        $('#quantity').val(data.quantity);
                        $('#comment').val(data.comment);

                        $("#hider").fadeIn("slow");
                        $('#itemspopup_box').fadeIn("slow");
                    },
                    'json'
                    );

        }
        ;

    });
</script>            