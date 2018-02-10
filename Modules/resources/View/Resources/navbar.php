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