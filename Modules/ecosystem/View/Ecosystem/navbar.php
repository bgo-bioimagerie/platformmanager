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