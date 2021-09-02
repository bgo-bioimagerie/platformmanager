<script>
    <?php
    include 'Modules/com/Api/ComnewsApi.php';
    ?>

    $(document).ready(function() {

        // $("#hider").hide();
        $("#hider").fadeIn("slow");
        $('#entriespopup_box').fadeIn("slow");
        $("#entriesbuttonclose").click(function() {
            $("#hider").hide();
            $('#entriespopup_box').hide();
        });
        showEditEntryForm(<?php echo $id_space ?>);

        /**
         * Gets bookingPrices from BookingpricesApi then calls displayPopup()
         * 
         * @param string id_space
         * @param string id_resource 
         * 
         */
        function showEditEntryForm(id_space) {
            <?php Configuration::getLogger()->debug("[TEST]", ["in showEditEntryForm"]); ?>

            // get com infos
            $.post('comgetnews/' + id_space)
                .done((response) => {
                    let data = JSON.parse(response);
                    let newsList = new Array();
                    data.forEach((elem) => {
                        newsList.push({
                            "title": elem.title,
                            "content": elem.content
                        })
                    });
                    fillPopup(newsList);
                });
        }

        /**
         * Displays a popup window #entriespopup_box
         * to edit resource prices
         * 
         * @param array newsList
         * 
         */
        function fillPopup(newsList) {
            newsList.forEach((news) => {
                $("#content_section").append("<b>" + news.title + "</b>");
                $("#content_section").append(news.content);
                $("#content_section").append("<br/>");
            });
        }
    });
</script>