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
                    <li>
                        <div class="text-right">
                            <a href="resourceshelp/{{id_space}}">
                                <span class="glyphicon glyphicon-question-sign"></span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="resources/{{id_space}}">{{Resources}}</a>
                            <a id="menu-button" href="resourcesedit/{{id_space}}/0">+</a>
                        </div>
                    </li>

                    <br/>
                    <li>
                        {{Sorting}}
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="reareas/{{id_space}}">{{Areas}}</a>
                            <a href="reareasedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="recategories/{{id_space}}">{{Categories}}</a>
                            <a href="recategoriesedit/{{id_space}}/0">+</a>
                        </div>
                    </li>

                    <br/>
                    <li>
                        {{Responsible}}
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="rerespsstatus/{{id_space}}">{{Resps_Status}}</a>
                            <a href="rerespsstatusedit/{{id_space}}/0">+</a>
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="resourcesvisa/{{id_space}}">{{Visas}}</a>
                            <a href="resourceseditvisa/{{id_space}}/0">+</a>
                        </div>
                    </li>

                    <br/>
                    <li>
                        {{Suivi}}
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="restates/{{id_space}}">{{States}}</a>
                            <a id="menu-button" href="restatesedit/{{id_space}}/0">+</a>
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="reeventtypes/{{id_space}}">{{Event_Types}}</a>
                            <a href="reeventtypesedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                </ul>
            </ul>
        </div>
    </div>
</nav>