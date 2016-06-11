<link rel="stylesheet" href="externals/dataTables/dataTables.bootstrap.css">

<script src="externals/jquery-1.11.1.js"></script>
<script src="externals/dataTables/jquery.dataTables.js"></script>
<script src="externals/dataTables/dataTables.fixedHeader.min.js"></script>
<script src="externals/dataTables/dataTables.fixedColumns.min.js"></script>
<script src="externals/dataTables/dataTables.bootstrap.js"></script>
<script>
$(document).ready(function() {
    $('#dataTable').DataTable( {
        scrollY:        "400px",
        scrollX:        true,
        scrollCollapse: true,
        paging:         false,
        fixedColumns:   {
            leftColumns: numFixedCol
        }
    } );
} );
</script>
<style>
    th, td { white-space: nowrap; }
    div.dataTables_wrapper {
        
        margin: 0 auto;
    }
</style>    