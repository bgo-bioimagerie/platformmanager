<nav class="navbar navbar-default" style="background-color: #ffffff; z-index: 12;">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#coremainmenusnavbar" aria-expanded="false" aria-controls="coremainmenusnavbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="coremainmenusnavbar">
            <ul class="nav navbar-nav">
                <li><a href="coremainmenus"><?php echo CoreTranslator::MainStructures($lang) ?></a></li>
                <li><a href="coremainsubmenus"><?php echo CoreTranslator::MainSubMenus($lang) ?></a></li>
                <li><a href="coremainmenuitems"><?php echo CoreTranslator::Items($lang) ?></a></li>
            </ul>                 
        </div>
    </div>
</nav>

