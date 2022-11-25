<?php
require_once 'Modules/core/Model/CoreUserSettings.php';
$userSettingsModel = new CoreUserSettings();
?>

    <link rel="stylesheet" type="text/css" href="externals/node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css">
    
    <script src="externals/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="externals/node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function () {
            //var tableHeight = window.innerHeight - 200;
            let defaultCol = 0;
            let defaultLen = 10;
            let tab = $('#tableID').DataTable({
                buttons: [
                    {
                        extend: 'csvHtml5',
                        className: 'btn-secondary',
                        text: 'export csv'
                    }
                ],
                columnDefs: [
                    {targets: 'no-sort', orderable: false, searchable: false}
                ],
                lengthMenu: [[10,  50, 100, -1], [10, 50, 100, 'All']],
                order: [[ defaultCol, "desc"]],
                pageLength: defaultLen
            });
            tab.buttons().container().appendTo( $(tab.table().container()) );
        });
    </script>
