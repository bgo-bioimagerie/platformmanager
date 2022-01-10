

    <link rel="stylesheet" type="text/css" href="externals/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="externals/dataTables/dataTables.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="externals/dataTables/fixedColumns.bootstrap.min.css">

    <script src="externals/dataTables/jquery-1.12.3.js"></script>
    <script src="externals/dataTables/jquery.dataTables.min.js"></script>
    <script src="externals/dataTables/dataTables.bootstrap.min.js"></script>
    <script src="externals/dataTables/dataTables.fixedColumns.min.js"></script>

    <script>
        $(document).ready(function () {
            //var tableHeight = window.innerHeight - 200;
            var table = $('#tableID').DataTable({
                columnDefs: [{targets: 'no-sort', orderable: false, searchable: false}],
            });
        });
    </script>
