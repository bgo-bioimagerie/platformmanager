<nav class="navbar navbar-expand-lg navbar-light bg-light" style="background-color: #ffffff; z-index: 12;">
    <div class="container-fluid">
            <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#coremainmenusnavbar" aria-expanded="false" aria-controls="coremainmenusnavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

        <div class="collapse navbar-collapse" id="coremainmenusnavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="coremainmenus"><?php echo CoreTranslator::MainMenus($lang) ?></a></li>
                <li class="nav-item"><a class="nav-link" href="coremainsubmenus"><?php echo CoreTranslator::MainSubMenus($lang) ?></a></li>
                <li class="nav-item"><a class="nav-link" href="coremainmenuitems"><?php echo CoreTranslator::Items($lang) ?></a></li>
            </ul>                 
        </div>
    </div>
</nav>

