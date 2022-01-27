

    <link rel="stylesheet" type="text/css" href="externals/node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">

    <script src="externals/dataTables/jquery-1.12.3.js"></script>
    <script src="externals/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="externals/node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            //var tableHeight = window.innerHeight - 200;
            $('#tableID').DataTable({
                columnDefs: [{targets: 'no-sort', orderable: false, searchable: false}],
            });
        });
    </script>
