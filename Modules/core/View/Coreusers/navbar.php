<nav class="navbar navbar-default navbar-fixed-top" style="margin-top: 50px; background-color: #ffffff; z-index: 12;">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="coremyaccount"><?php echo CoreTranslator::My_Account($lang) ?></a>
            
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#spacenavbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <div class="text-center">
            <ul class="nav navbar-nav">
                <li><a href="usersmyaccount"><?php echo CoreTranslator::Informations($lang) ?></a></li>
                <li><a href="coremyaccount"><?php echo CoreTranslator::Password($lang) ?></a></li>
            </ul>                 
        </div>
    </div>
</nav>