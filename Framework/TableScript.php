<?php
require_once 'Modules/core/Model/CoreUserSettings.php';
$userSettingsModel = new CoreUserSettings();
?>

    <link rel="stylesheet" type="text/css" href="externals/node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">

    <script src="externals/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="externals/node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            //var tableHeight = window.innerHeight - 200;
            let defaultCol = 0;
            let defaultLen = 10;
            $('#tableID').DataTable({
                columnDefs: [
                    {targets: 'no-sort', orderable: false, searchable: false}
                ],
                lengthMenu: [[10,  50, 100, -1], [10, 50, 100, 'All']],
                order: [[ defaultCol, "desc"]],
                pageLength: defaultLen
            });
        });
    </script>
