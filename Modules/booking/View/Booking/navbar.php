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
                        {{Calendar_View}}
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a href="bookingscheduling/{{id_space}}/0">{{Scheduling}}</a>   
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="bookingdisplay/{{id_space}}">{{Display}}</a>
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="bookingaccessibilities/{{id_space}}">{{Accessibilities}}</a>
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="bookingnightwe/{{id_space}}">{{Nightwe}}</a>
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="bookingcolorcodes/{{id_space}}">{{Color_codes}}</a>
                            <a id="menu-button" href="bookingcolorcodeedit/{{id_space}}/0">+</a>
                        </div>
                    </li>
                    <br/>
                    <li>
                        {{Additional_info}}
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="bookingsupsinfo/{{id_space}}">{{SupplementariesInfo}}</a>
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="bookingpackages/{{id_space}}">{{Packages}}</a>
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="bookingquantities/{{id_space}}">{{Quantities}}</a>
                        </div>
                    </li>

                    <br/>
                    <li>
                        {{booking}}
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="bookingblock/{{id_space}}">{{Block_Resouces}}</a>
                        </div>
                    </li>
                </ul>
            </ul>
        </div>
    </div>
</nav>