<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('stylesheet') ?>
<link rel="stylesheet" type="text/css" href="externals/node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">

<script src="externals/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="externals/node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<?php endblock() ?>
    
<?php startblock('content') ?>

    <script>
        $(document).ready(function () {
            var table = $('#antibodies').DataTable({
                scrollY: "700px",
                scrollX: true,
                scrollCollapse: true,
                paging: false,
                fixedColumns: {
                    leftColumns: 0
                }
            });
        });
    </script>

<div class="row pm-table">
    <div class="col-12">
        <div class="row mb-3">
            <div class="col-4">
                <div class="dropdown">
                    <button id="antibodiesmenu" type="button" class="btn  btn-primary" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo 'Menu ' . AntibodiesTranslator::antibodies($lang) ?>
                    </button>
                    <div class="dropdown-menu col-12" aria-labelledby="antibodiesmenu" style="background-color: transparent; border: none; box-shadow:none;">
                        <?php
                        $c = new AntibodiesController(new Request([], false));
                        echo $c->dropDownMenu($id_space);
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-2">
                <button type="button" onclick="location.href = 'anticorpscsv/<?php echo $id_space ?>'" class="btn btn-primary"><?php echo AntibodiesTranslator::Export_as_csv($lang) ?></button>
            </div>
        </div>
    </div>
    <div class="col-12 text-center">
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-dark <?php
            if ($letter == "All") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/All';"><?php echo AntibodiesTranslator::All($lang) ?></button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "A") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/A';">A</button>
            <button class="btn btn-outline-dark <?php
                    if ($letter == "B") {
                        echo "active";
                    }
                    ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/B';">B</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "C") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/C';">C</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "D") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/D';">D</button>
            <button class="btn btn-outline-dark <?php
                    if ($letter == "E") {
                        echo "active";
                    }
                    ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/E';">E</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "F") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/F';">F</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "G") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/G';">G</button>
            <button class="btn btn-outline-dark <?php
                    if ($letter == "H") {
                        echo "active";
                    }
                    ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/H';">H</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "I") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/I';">I</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "J") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/J';">J</button>
            <button class="btn btn-outline-dark <?php
                    if ($letter == "K") {
                        echo "active";
                    }
                    ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/K';">K</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "L") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/L';">L</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "M") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/M';">M</button>
            <button class="btn btn-outline-dark <?php
                    if ($letter == "N") {
                        echo "active";
                    }
                    ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/N';">N</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "O") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/O';">O</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "P") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/P';">P</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "Q") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/Q';">Q</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "R") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/R';">R</button>
            <button class="btn btn-outline-dark <?php
            if ($letter == "S") {
                echo "active";
            }
            ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/S';">S</button>
            <button class="btn btn-outline-dark <?php
                if ($letter == "T") {
                    echo "active";
                }
                ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/T';">T</button>
            <button class="btn btn-outline-dark <?php
                if ($letter == "U") {
                    echo "active";
                }
                ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/U';">U</button>
            <button class="btn btn-outline-dark <?php
                if ($letter == "V") {
                    echo "active";
                }
                ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/V';">V</button>
            <button class="btn btn-outline-dark <?php
                if ($letter == "W") {
                    echo "active";
                }
                ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/W';">W</button>
            <button class="btn btn-outline-dark <?php
                if ($letter == "X") {
                    echo "active";
                }
                ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/X';">X</button>
            <button class="btn btn-outline-dark <?php
                if ($letter == "Y") {
                    echo "active";
                }
                ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/Y';">Y</button>
            <button class="btn btn-outline-dark <?php
                if ($letter == "Z") {
                    echo "active";
                }
                ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/Z';">Z</button>
        </div>

    </div>

    <br/>
    <div class="col-12">
        <div class="row">
            <div class="page-header" style="margin-top: -20px;">
                <h1>
                    Anticorps<br> <small></small>
                </h1>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <form role="form" class="form-horizontal" action="anticorpsadvsearchquery/<?php echo $id_space ?>"
                    method="post">

                <?php
                if (!isset($searchName)) {
                    $searchName = "";
                }
                if (!isset($searchNoH2P2)) {
                    $searchNoH2P2 = "";
                }
                if (!isset($searchSource)) {
                    $searchSource = "";
                }
                if (!isset($searchCible)) {
                    $searchCible = "";
                }
                if (!isset($searchValide)) {
                    $searchValide = "";
                }
                if (!isset($searchResp)) {
                    $searchResp = "";
                }
                if (!isset($searchColumn)) {
                    $searchColumn = "";
                }
                if (!isset($searchCom)) {
                    $searchCom = "";
                }
                ?>
                    <div class="col-1">
                        <label class="form-label">Recherche Avancée:</label>
                    </div>

                    <div class="col-11">
                        <div class="row mb-2">
                            <label class="form-label col-1" style="margin:0px;">Nom:</label>
                            <div class="col-3">
                                <input class="form-control" id="searchName" type="text" name="searchName" value="<?php echo $searchName ?>"
                                        />
                            </div>
                            <label for="inputEmail" class="form-label col-1">No H2P2:</label>
                            <div class="col-2">
                                <input class="form-control" id="searchNoH2P2" type="text" name="searchNoH2P2" value="<?php echo $searchNoH2P2 ?>"
                                        />
                            </div>
                            <label for="inputEmail" class="form-label col-1">Source:</label>
                            <div class="col-3">
                                <input class="form-control" id="searchSource" type="text" name="searchSource" value="<?php echo $searchSource ?>"
                                        />
                            </div>
                        </div>

                        <div class="row mb-2">	
                            <label for="inputEmail" class="form-label col-1">Tissu cible:</label>
                            <div class="col-3">
                                <input class="form-control" id="searchCible" type="text" name="searchCible" value="<?php echo $searchCible ?>"
                                        />
                            </div>

                            <label for="inputEmail" class="form-label col-1">Statut:</label>
                            <div class="col-2">
                                <select class="form-select" id="searchValide" name="searchValide">
                                    <OPTION value="0" <?php
                                    if ($searchColumn == "0") {
                                        echo $selected;
                                    }?> >  </OPTION>
                                    <OPTION value="1" <?php
                                    if ($searchValide == "1") {
                                        echo $selected;
                                    }?> > Validé </OPTION>
                                    <OPTION value="2" <?php
                                    if ($searchValide == "2") {
                                        echo $selected;
                                    }?> > Non validé </OPTION>
                                    <OPTION value="3" <?php
                                    if ($searchValide == "3") {
                                        echo $selected;
                                    }?> > Non testé </OPTION>
                                </select>
                            </div>

                            <label for="inputEmail" class="form-label col-1">Propriétaire:</label>
                            <div class="col-3">
                                <input class="form-control" id="searchResp" type="text" name="searchResp" value="<?php echo $searchResp ?>"
                                        />
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="form-label col-2">Commentaire:</label>
                            <div class="col-4">
                                <input class="form-control" id="searchCom" type="text" name="searchCom" value="<?php echo $searchCom ?>"
                                        />
                            </div>
                            <div class="col" id="button-div">
                                <input type="submit" class="btn btn-primary" value="Rechercher" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
                        </div>

        <div class="row">
            <div class="col-12">
            <table aria-label="list of antibodies" id="antibodies" class="table table-striped table-bordered" style="font-size: 10px;">
                <thead>	 
                    <tr>
                        <th id="antibody" class="text-center" colspan="9" style="color:#337AB7;">Anticorps</th>
                        <th id="protocol" class="text-center" colspan="2" style="background-color: #ffeeee; color:#337AB7;">Protocole</th>
                        <th id="tissues" class="text-center" colspan="7" style="background-color: #eeffee; color:#337AB7;">Tissus</th>
                        <th id="owner" class="text-center" colspan="4" style="background-color: #eeeeff; color:#337AB7;">Propriétaire</th>
                    </tr>

                    <tr>		
                        <th id="number" class="text-center" style="color:#337AB7;">No</th> 
                        <th id="name" class="text-center" style="color:#337AB7;">Nom</th>
                        <th id="temperature" class="text-center" style="color:#337AB7;">St</th>
                        <th id="provider" class="text-center" style="color:#337AB7;">Fournisseur</th>
                        <th id="source" class="text-center" style="color:#337AB7;">Source</th>
                        <th id="ref" class="text-center" style="color:#337AB7;">Référence</th>
                        <th id="clone" class="text-center" style="color:#337AB7;">Clone</th>
                        <th id="batch" class="text-center" style="color:#337AB7;">lot</th>
                        <th id="isotype" class="text-center" style="color:#337AB7;">Isotype</th>

                        <th id="proto" class="text-center" style="background-color: #ffeeee; color:#337AB7;">proto</th>
                        <th id="aci" class="text-center" style="background-color: #ffeeee; color:#337AB7;">AcI dil</th>

                        <th id="image" class="text-center" style="background-color: #eeffee; color:#337AB7;"></th>
                        <th id="comment" class="text-center" style="background-color: #eeffee; color:#337AB7;">commentaire</th>
                        <th id="species" class="text-center" style="background-color: #eeffee; color:#337AB7;">espèce</th>
                        <th id="organ" class="text-center" style="background-color: #eeffee; color:#337AB7;">organe</th>
                        <th id="status" class="text-center" style="background-color: #eeffee; color:#337AB7;">statut</th>
                        <th id="refblock" class="text-center" style="background-color: #eeffee; color:#337AB7;">ref. bloc</th>
                        <th id="sample" class="text-center" style="background-color: #eeffee; color:#337AB7;">prélèvement</th>	

                        <th id="name" class="text-center" style="background-color: #eeeeff; color:#337AB7;">Nom</th>
                        <th id="available" class="text-center" style="background-color: #eeeeff; color:#337AB7;">disponibilité</th>
                        <th id="date" class="text-center" style="background-color: #eeeeff; color:#337AB7;">Date réception</th>
                        <th id="case" class="text-center" style="background-color: #eeeeff; color:#337AB7;">No Dossier</th>

                    </tr>
                </thead>
                <tbody>
<?php foreach ($anticorpsArray as $anticorps) : ?> 

                        <tr>
                                <?php
                                $anticorpsId = $this->clean($anticorps['id']);
                                $isCatalogue = "";
                                if ($anticorps['export_catalog'] == 1) {
                                    $isCatalogue = " (c)";
                                }
                                ?>

                            <td headers="antibody number"  class="text-left"><a href="anticorpsedit/<?php echo $id_space ?>/<?php echo $anticorpsId ?>"><?php echo $this->clean($anticorps ['no_h2p2']); ?></a></td>
                            <td headers="antibody name"  class="text-left"><a href="anticorpsedit/<?php echo $id_space ?>/<?php echo $anticorpsId ?>"><?php echo $this->clean($anticorps ['nom']); ?></a></td>
                            <td headers="antibody temperature"  class="text-left"><?php echo $this->clean($anticorps ['stockage']); ?></td>
                            <td headers="antibody provider"  class="text-left"><?php echo $this->clean($anticorps ['fournisseur']); ?></td>
                            <td headers="antibody source"  class="text-left"><?php echo $this->clean($anticorps ['source']); ?></td>
                            <td headers="antibody ref"  class="text-left"><?php echo $this->clean($anticorps ['reference']); ?></td>
                            <td headers="antibody clone"  class="text-left"><?php echo $this->clean($anticorps ['clone']); ?></td>
                            <td headers="antibody batch"  class="text-left"><?php echo $this->clean($anticorps ['lot']); ?></td>
                            <td headers="antibody isotype"  class="text-left"><?php echo $this->clean($anticorps ['isotype']); ?></td>



                            <!--  PROTOCOLE -->
                            <td headers="protocol proto"  class="text-left" style="background-color: #ffeeee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {

                                    if ($tissus[$i]['ref_protocol'] == "0") {
                                        $val .= "<p>Manuel</p>";
                                    } else {
                                        $val .= "<p><a href=\"protocolsedit/".$id_space. "/". $tissus[$i]['id_protocol'] . "\">"
                                                . $tissus[$i]['ref_protocol'] . "</a></p>";
                                    }
                                }
                                echo $val;
                                ?></td>


                            <td headers="protocol aci"  class="text-left" style="background-color: #ffeeee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {
                                    $val = $val . "<p>"
                                            . $tissus[$i]['dilution']
                                            . "</p>";
                                }
                                echo $val;
                                ?></td>


                            <!-- TISSUS -->
                            <td headers="tissues image" class="text-left" style="background-color: #eeffee;">

                                <?php
                                foreach ($anticorps['tissus'] as $tissus) {
                                    $imageFile = "data/antibodies/" . $tissus["image_url"];
                                    $printImage = true;
                                    if (!file_exists($imageFile) || is_dir($imageFile)) {
                                        $printImage = false;
                                        //$imageFile = "Modules/antibodies/Theme/images_icon.png";
                                    } else {
                                        list($width, $height, $type, $attr) = getimagesize($imageFile);
                                    }
                                    if ($printImage) {
                                        ?>
                                        <a id="imgview_<?php echo $tissus["id"] ?>" >
                                            <img src="<?php echo $imageFile ?>" itemprop="thumbnail" alt="photo" width="25" height="25"/>
                                            <br/>
                                        </a>
                                        <script>
                                            $(document).ready(function () {
                                                $('#imgview_<?php echo $tissus["id"] ?>').on('click', function () {

                                                    var img = "<p><?php echo $tissus["image_url"] ?></p><img src='<?php echo $imageFile ?>' width='100%'  />";

                                                    $('#imagedivcontent').html(img);
                                                    $('#imagepopup_box').show();
                                                    $('#hider').show();
                                                });
                                            });
                                        </script>
            <?php
        }
    }
    ?>
                            </td>
                            
                            <td headers="tissues comment"  class="text-left" style="background-color: #eeffee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {
                                    $val = $val . "<p>"
                                            . $tissus[$i]['comment']
                                            . "</p>";
                                }
                                echo $val;
                                ?></td>

                            <td headers="tissues species"  class="text-left" style="background-color: #eeffee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {
                                    $val = $val . "<p>" . $tissus[$i]['espece']
                                            . "</p>";
                                }
                                echo $val;
                                ?></td>

                            <td headers="tissues organ"  class="text-left" style="background-color: #eeffee;"><?php
                            $tissus = $anticorps ['tissus'];
                            $val = "";
                            for ($i = 0; $i < count($tissus); ++$i) {
                                $val = $val . "<p>"
                                        . $tissus[$i]['organe']
                                        . "</p>";
                            }
                            echo $val;
                                ?></td>

                            <td headers="tissues status"  class="text-left" style="background-color: #eeffee;"><?php
                                $tissus = $anticorps ['tissus'];
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
                                ?></td>


                            <td headers="tissues refblock"  class="text-left" style="background-color: #eeffee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {
                                    $val = $val . "<p>"
                                            . $tissus[$i]['ref_bloc']
                                            . "</p>";
                                }
                                echo $val;
                                ?></td>

                            <td headers="tissues sample"  class="text-left" style="background-color: #eeffee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {
                                    $val = $val . "<p>"
                                            . $tissus[$i]['prelevement']
                                            . "</p>";
                                }
                                echo $val;
                                ?></td>




                            <td headers="owner name"  class="text-left" style="width:5em; background-color: #eeeeff;"><?php
                                $owner = $anticorps ['proprietaire'];
                                foreach ($owner as $ow) {
                                    $name = $ow['name'] . " " . $ow['firstname'];
                                    $dispo = $ow['disponible'];
                                    if ($dispo == 1) {
                                        $dispo = "disponible";
                                    } else if ($dispo == 2) {
                                        $dispo = "épuisé";
                                    } else if ($dispo == 3) {
                                        $dispo = "récupéré par équipe";
                                    }
                                    $date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
                                    $txt = $this->clean($name);

                                    if ($this->clean($dispo) == "épuisé") {
                                        echo '<p style="background-color:#ffaaaa; color:#fff">' . $txt . '</p>';
                                    } else {
                                        echo '<p>' . $txt . '</p>';
                                    }
                                }
                                ?>
                            </td>

                            <td headers="owner available"  class="text-left" style="background-color: #eeeeff;"><?php
                                $owner = $anticorps ['proprietaire'];
                                foreach ($owner as $ow) {
                                    $dispo = $ow['disponible'];
                                    if ($dispo == 1) {
                                        $dispo = "disponible";
                                    } else if ($dispo == 2) {
                                        $dispo = "épuisé";
                                    } else if ($dispo == 3) {
                                        $dispo = "récupéré par équipe";
                                    }
                                    $date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
                                    $txt = $this->clean($dispo);

                                    if ($this->clean($dispo) == "épuisé") {
                                        echo '<p style="background-color:#ffaaaa; color:#fff">' . $txt . '</p>';
                                    } else {
                                        echo '<p>' . $txt . '</p>';
                                    }
                                }
                                ?>
                            </td>

                            <td headers="owner date"  class="text-left" style="background-color: #eeeeff;"><?php
                                $owner = $anticorps ['proprietaire'];
                                foreach ($owner as $ow) {
                                    $name = $ow['name'] . " " . $ow['firstname'];
                                    $dispo = $ow['disponible'];
                                    if ($dispo == 1) {
                                        $dispo = "disponible";
                                    } else if ($dispo == 2) {
                                        $dispo = "épuisé";
                                    } else if ($dispo == 3) {
                                        $dispo = "récupéré par équipe";
                                    }
                                    $date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
                                    $txt = $this->clean($date_recept);

                                    if ($this->clean($dispo) == "épuisé") {
                                        echo '<p style="background-color:#ffaaaa; color:#fff">' . $txt . '</p>';
                                    } else {
                                        echo '<p>' . $txt . '</p>';
                                    }
                                }
                                ?>
                            </td>

                            <td headers="owner case"  class="text-left" style="background-color: #eeeeff;"><?php
                                $owner = $anticorps ['proprietaire'];
                                foreach ($owner as $ow) {
                                    $name = $ow['name'] . " " . $ow['firstname'];
                                    $dispo = $ow['disponible'];
                                    if ($dispo == 1) {
                                        $dispo = "disponible";
                                    } else if ($dispo == 2) {
                                        $dispo = "épuisé";
                                    } else if ($dispo == 3) {
                                        $dispo = "récupéré par équipe";
                                    }
                                    $date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
                                    $txt = $this->clean($ow['no_dossier']);

                                    if ($this->clean($dispo) == "épuisé") {
                                        echo '<p style="background-color:#ffaaaa; color:#fff">' . $txt . '</p>';
                                    } else {
                                        echo '<p>' . $txt . '</p>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
<?php endforeach; ?>

                </tbody>

            </table>
        </div>
        </div>
    </div>
</div>


<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->
<link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">
<div id="hider" class="col-12"></div> 
<div id="imagepopup_box" class="pm_popup_box_full" style="display: none;">
    <div class="col-1 offset-11" style="text-align: right;"><a id="tissusbuttonclose" class="bi-x-circle-fill" style="cursor:pointer;"></a></div>
    <div id="imagedivcontent">

    </div>    
</div> 

<script>
    $(document).ready(function () {

        $("#hider").hide();
        $("#imagepopup_box").hide();

        $("#tissusbuttonclose").click(function () {
            $("#hider").hide();
            $('#imagepopup_box').hide();
        });

    });
</script>

<?php endblock(); ?>
