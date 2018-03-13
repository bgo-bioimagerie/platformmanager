<nav class="navbar navbar-default navbar-fixed-top" style="margin-top: 50px; background-color: #ffffff; z-index: 12;">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#spacenavbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <div class="text-center">
            <ul class="nav navbar-nav">
                <?php
                foreach( $mainSubMenus as $menu ){
                    ?>
                    <li><a href="coretiles/2/<?php echo $menu["id"] ?>" ><?php echo $menu["name"] ?></a></li>
                    <?php
                }
                ?>
            </ul>                 
        </div>
    </div>
</nav>

