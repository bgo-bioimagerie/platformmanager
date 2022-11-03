<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#spacenavbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
        </button>

        <div id="navbar" class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php
                foreach( $mainSubMenus as $menu ){
                    ?>
                    <li class="nav-item"><a class="nav-link" href="coretiles/2/<?php echo $menu["id"] ?>" ><?php echo $menu["name"] ?></a></li>
                    <?php
                }
                ?>
            </ul>                 
        </div>
    </div>
</nav>

