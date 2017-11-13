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
            <ul class="nav navbar-nav" >
                <li>
                    <a style="background-color:{{bgcolor}}; color: #fff; margin-left: -14px;" href=""> {{title}} <span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon {{glyphicon}}"></span></a>
                </li>
                <ul class="pm-nav-li">
                    <li >
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="ecbelongings/{{id_space}}">{{Belongings}}</a>
                            <a href="ecbelongingsedit/{{id_space}}/0">+</a>   
                        </div>
                    </li>

                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="ecunits/{{id_space}}">{{Units}}</a>
                            <a href="ecunitsedit/{{id_space}}/0">+</a>   
                        </div>
                    </li>

                    <br/>
                    <li>
                        {{Users}}
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a href="ecusersedit/{{id_space}}/0">{{Neww}}</a>   
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="ecactiveusers/{{id_space}}">{{Active}}</a>
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="ecunactiveusers/{{id_space}}">{{Unactive}}</a>
                        </div>
                    </li>
                    <br/>
                    <li>{{Export}}</li>
                    <li>
                        <div class="pm-inline-div">
                            <a href="ecexportresponsible/{{id_space}}">{{Responsible}}</a>    
                        </div>
                        <div class="pm-inline-div">
                            <a href="ecexportall/{{id_space}}">{{ExportAll}}</a>    
                        </div>

                    </li>
                </ul>
            </ul>
        </div>
    </div>
</nav>