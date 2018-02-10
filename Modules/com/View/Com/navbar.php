<nav class="navbar navbar-default sidebar" role="navigation" style="border: none;">
    <div class="container">
        <div class="navbar-header" style="background-color: #e7ecf0;">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-sidebar-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>      
        </div>
        <div class="collapse navbar-collapse" style="border: none;">
            <ul class="nav navbar-nav" style="width: 25%" id="bs-sidebar-navbar-collapse-1" >
                <li style="width: 100%">
                    <a  style="background-color:{{bgcolor}}; color: #fff;" href=""> {{title}} 
                    <span style="color: #fff; font-size:16px; float:right;" class=" hidden-xs showopacity glyphicon {{glyphicon}}"></span>
                    </a>
                </li>
                <ul class="pm-nav-li">
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="comtileedit/{{id_space}}">{{Tilemessage}}</a>
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="comnews/{{id_space}}">{{News}}</a>
                            <a id="menu-button" href="comnewsedit/{{id_space}}/0">+</a>
                        </div>
                    </li>
                </ul>
            </ul>
        </div>
    </div>
</nav>