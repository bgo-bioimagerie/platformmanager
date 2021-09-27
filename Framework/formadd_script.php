<script>
    function addRow(tableID) {

        var idx = 1;
        if (tableID === "tableIDname") {
            idx = 1;
        }
        var table = document.getElementById(tableID);

        var rowCount = table.rows.length;
        //document.write(rowCount);
        var row = table.insertRow(rowCount);
        //document.write(row);
        var colCount = table.rows[idx].cells.length;
        //document.write(colCount);

        for (var i = 0; i < colCount; i++) {

            var newcell = row.insertCell(i);

            newcell.innerHTML = table.rows[idx].cells[i].innerHTML;
            //alert(newcell.childNodes);
            switch (newcell.childNodes[0].type) {
                case "date":
                    newcell.childNodes[0].value = "";
                    break;
                case "text":
                    newcell.childNodes[0].value = "";
                    break;
                case "number":
                    newcell.childNodes[0].value = "";
                    break;  
                case "hidden":
                    newcell.childNodes[0].value = "";
                    break;      
                case "checkbox":
                    newcell.childNodes[0].checked = false;
                    break;
                case "select-one":
                    newcell.childNodes[0].selectedIndex = 0;
                    break;
            }
        }
    }

    function deleteRow(tableID) {
        try {

            var idx = 2;
            if (tableID === "tableIDname") {
                idx = 2;
            }
            var table = document.getElementById(tableID);
            var rowCount = table.rows.length;

            for (var i = 0; i < rowCount; i++) {
                var row = table.rows[i];
                var chkbox = row.cells[0].childNodes[0];
                if (null != chkbox && true == chkbox.checked) {
                    if (rowCount <= idx) {
                        alert("Cannot delete all the rows.");
                        break;
                    }
                    table.deleteRow(i);
                    rowCount--;
                    i--;
                }
            }
        } catch (e) {
            alert(e);
        }
    }

</script>