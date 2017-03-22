<?php include 'Modules/core/View/layout.php' ?>

<?php include('Modules/catalog/View/Catalogview/toolbar.php') ?>


<head>

    <link rel="stylesheet" href="externals/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="externals/dataTables/dataTables.bootstrap.css">
    <link rel="stylesheet" href="externals/dataTables/dataTables.fixedHeader.css">

    <script src="externals/jquery-1.11.1.js"></script>
    <script src="externals/dataTables/jquery.dataTables.js"></script>
    <script src="externals/dataTables/dataTables.fixedHeader.min.js"></script>
    <script src="externals/dataTables/dataTables.bootstrap.js"></script>

    <style>
        body { font-size: 120%; padding: 1em; margin-top:30px; margin-left: -15px;}
        div.FixedHeader_Cloned table { margin: 0 !important }

        table{
            white-space: nowrap;
        }

        thead tr{
            height: 50px;
        }

    </style>

    <script>
        $(document).ready(function () {
            $('#example').dataTable({
                "aoColumns": [
                    {"bSearchable": true},
                    null,
                    {"bSearchable": true},
                    {"bSearchable": true},
                    {"bSearchable": true},
                    {"bSearchable": true},
                    {"bSearchable": true},
                    {"bSearchable": true},
                    {"bSearchable": true},
                    {"bSearchable": true},
                    {"bSearchable": true}
                ],
                "lengthMenu": [[100, 200, 300, -1], [100, 200, 300, "All"]]
            }
            );
        });
    </script>

    <script>
        $(document).ready(function () {
            var table = $('#example').DataTable();
            new $.fn.dataTable.FixedHeader(table, {
                alwaysCloneTop: true
            });

        });
    </script>



</head>


<div class="col-md-12" style="height:15px; background-color: #ffffff;">

</div>

<div class="col-md-12" style="background-color: #ffffff;">

    <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>

            <tr>
                <th class="text-center" colspan="8" style="width:75%; color:#337AB7;">Anticorps</th>
                <th class="text-center" colspan="3" style="width:25%; background-color: #eeffee; color:#337AB7;">Tissus</th>
            </tr>

            <tr>
                <th class="text-center" style="width:5%; color:#337AB7;"></th>
                <th class="text-center" style="width:5%; color:#337AB7;">No</th>
                <th class="text-center" style="width:5%; color:#337AB7;"><?php echo CatalogTranslator::Name($lang) ?></th>
                <th class="text-center" style="width:5%; color:#337AB7;"><?php echo CatalogTranslator::Application($lang) ?></th>
                <th class="text-center" style="width:5%; color:#337AB7;"><?php echo CatalogTranslator::Staining($lang) ?></th>
                <th class="text-center" style="width:5%; color:#337AB7;"><?php echo CatalogTranslator::Provider($lang) ?></th>
                <th class="text-center" style="width:5%; color:#337AB7;"><?php echo CatalogTranslator::Reference($lang) ?></th>
                <th class="text-center" style="width:5%; color:#337AB7;"><?php echo CatalogTranslator::Source($lang) ?></th>               
                <th class="text-center" style="width:5%; background-color: #eeffee; color:#337AB7;"><?php echo CatalogTranslator::Spices($lang) ?></th>
                <th class="text-center" style="width:5%; background-color: #eeffee; color:#337AB7;"><?php echo CatalogTranslator::Sample($lang) ?></th>
                <th class="text-center" style="width:5%; background-color: #eeffee; color:#337AB7;"><?php echo CatalogTranslator::Status($lang) ?></th>
            </tr>    

        </thead>

        <tbody>
            <?php foreach ($entries as $entry) : ?> 
                <tr>
                    <td width="10%" class="text-left">
                        <?php
                        $imageFile = "data/antibodies/" . $entry["image_url"];
                        if (!file_exists($imageFile) || is_dir($imageFile)) {
                            ?>
                            <div style="height:25px;"></div>
                            <?php
                        } else {
                            list($width, $height, $type, $attr) = getimagesize($imageFile);
                            ?>
                            <a href="<?php echo $imageFile ?>" itemprop="contentUrl" data-size="<?php echo $width ?>x<?php echo $height ?>">
                                <img src="<?php echo $imageFile ?>" itemprop="thumbnail" alt="photo" width="25" height="25"/>
                            </a>
                            <?php
                        }
                        ?>
                    </td>
                    <td width="10%" class="text-left"><?php echo $this->clean($entry ['no_h2p2']); ?></td>
                    <td width="10%" class="text-left"><?php echo $this->clean($entry ['nom']); ?></td> 
                    <td width="10%" class="text-left"><?php echo $this->clean($entry ['application']); ?></td> 
                    <td width="10%" class="text-left"><?php echo $this->clean($entry ['staining']); ?></td>
                    <td width="10%" class="text-left"><?php echo $this->clean($entry ['fournisseur']); ?></td> 
                    <td width="10%" class="text-left"><?php echo $this->clean($entry ['reference']); ?></td> 
                    <td width="5%" class="text-left"><?php echo $this->clean($entry ['source']); ?></td>

                    <!-- Tissus -->

                    <td width="10%" class="text-left" style="background-color: #eeffee;">
                        <?php
                        $tissus = $entry ['tissus'];
                        $val = "";
                        for ($i = 0; $i < count($tissus); ++$i) {
                            $val = $val . "<p>" . $tissus[$i]['espece']
                                    . "</p>";
                        }
                        echo $val;
                        ?>
                    </td>

                    <td width="10%" class="text-left" style="background-color: #eeffee;"><?php
                        $tissus = $entry ['tissus'];
                        $val = "";
                        for ($i = 0; $i < count($tissus); ++$i) {
                            $val = $val . "<p>"
                                    . $tissus[$i]['prelevement']
                                    . "</p>";
                        }
                        echo $val;
                        ?>
                    </td>

                    <td width="10%;" class="text-left" style="background-color: #eeffee;">
                        <?php
                        $tissus = $entry ['tissus'];
                        $val = "";
                        for ($i = 0; $i < count($tissus); ++$i) {

                            $statusTxt = "";
                            $background = "#ffffff";
                            foreach ($status as $stat) {
                                if ($tissus[$i]['status'] == $stat["id"]) {
                                    $statusTxt = $stat['nom'];
                                    $background = $stat["color"];
                                }
                            }
                            $val = $val . "<p style=\"background-color: #" . $background . "\">"
                                    . $statusTxt
                                    . "</p>";
                        }
                        echo $val;
                        ?>
                    </td>

                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
</div>
