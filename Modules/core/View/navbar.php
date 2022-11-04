<!-- Fixed navbar -->
<nav class="navbar sticky-top navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">PFM</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
        </button>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="coretiles"><span class="bi-list"></span></a></li>
                <?php
                if (count($toolMenu) > 5) {
                    ?>
                    <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="menudrop" data-bs-toggle="dropdown" role="button" aria-expanded="false"><?php echo  CoreTranslator::Menus($lang) ?></a>
                    <ul class="dropdown-menu" aria-labelledby="menudrop" role="menu">
                        <?php
                            foreach ($toolMenu as $tool) {
                                echo '<li><a class="dropdown-item" href="coretiles/1/'.$tool["id"].'">'.$tool["name"]."</a></li>\n";
                            }
                    ?>
                    </ul>
                </li>
                <?php
                } else {
                    for ($i = 0 ; $i < count($toolMenu) ; $i++) {
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="coretiles/1/<?php echo $toolMenu[$i]["id"] ?>" > <?php echo $toolMenu[$i]["name"] ?></a>
                        </li>
                <?php
                    }
                }
                ?>
                <li class="nav-item"><a class="nav-link" href="core/plans">Pricing</a></li>
                <?php
                if ($toolAdmin) {
                    ?>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="adminmenudrop" data-bs-toggle="dropdown" role="button" aria-expanded="false"><?php echo  CoreTranslator::Admin($lang) ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu" aria-labelledby="adminmenudrop" role="menu">
                      <?php
                            foreach ($toolAdmin as $tool) {
                                $key = $tool['link'];
                                $value = $tool['name'];
                                echo "<li><a class=\"dropdown-item\" href=\"/$key\"> $value </a></li>";
                            }
                    ?>
                    </ul>
                </li>
                <?php }?>
            </ul>
            <ul class="navbar-nav navbar-right">
                <li class="nav-item form-check form-switch">
                    <input class="nav-link form-check-input" <?php if ($theme == 'dark') {
                        echo "checked";
                    } ?> type="checkbox" id="flexSwitchCheckDefault" onclick="window.location.href=window.location.pathname+'?theme=switch'">
                </li>


                <?php if ($impersonate!=null) { ?><li class="nav-item"><a href="corespaceaccess/0/unimpersonate"><button class="btn btn-danger">Log back to <?php echo $impersonate; ?></button></a></li><?php } ?>
                <?php if (isset($_SESSION["login"]) && $_SESSION["id_user"] > 0) { ?>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="userdropmenu" data-bs-toggle="dropdown" role="button" aria-expanded="false"><img onerror="this.style.display='none'" alt="avatar" src="<?php echo "https://www.gravatar.com/avatar/" . md5(strtolower(trim($_SESSION['email']))) . "?s=20"; ?>"/> <?php echo  $userName ?></span></a>
                    <ul class="dropdown-menu" aria-labelledby="userdropmenu" role="menu">
                        <li><a class="dropdown-item" href="coremyaccount" > <?php echo  CoreTranslator::My_Account($lang) ?> </a></li>
                        <li><a class="dropdown-item" href="coresettings" > <?php echo  CoreTranslator::Settings($lang) ?> </a></li>
                        <li class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="corelogout"> <?php echo  CoreTranslator::logout($lang) ?> </a></li>
                    </ul>
                </li>
                <?php } else { ?>
                    <li class="nav-item"><a class="nav-link" href="coreconnection">Login</a></li>
                    <?php if (intval(Configuration::get('allow_registration', 0)) == 1) { ?><li class="nav-item"><a class="nav-link" href="corecreateaccount"><?php echo CoreTranslator::CreateAccount($lang) ?></a></li><?php } ?>
                <?php }?>
            </ul>
        </div>
    </div>
</nav>

