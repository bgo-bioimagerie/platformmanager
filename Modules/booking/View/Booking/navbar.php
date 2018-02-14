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
                            <a id="menu-button" href="bookingrestrictions/{{id_space}}">{{Restrictions}}</a>
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