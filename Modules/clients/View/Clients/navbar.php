<nav class="navbar navbar-default sidebar" style="border: 1px solid #f1f1f1;" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header" style="background-color: #e1e1e1;">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-sidebar-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>      
        </div>
        <div class="collapse navbar-collapse"  id="bs-sidebar-navbar-collapse-1">
            <ul class="nav navbar-nav" style="margin-left: -14px;">
                <li>
                    <a style="background-color:{{bgcolor}}; color: #fff; margin-left: -14px;" href=""> {{title}} <span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon {{glyphicon}}"></span></a>
                </li>
                <ul class="pm-nav-li">
                    
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="clclients/{{id_space}}">{{Clients}}</a>
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="clpricings/{{id_space}}">{{Pricings}}</a>
                        </div>
                    </li>
                    
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="clcompany/{{id_space}}">{{CompanyInfo}}</a>
                        </div>
                    </li>
                    
                </ul>
            </ul>
        </div>
    </div>
</nav>


