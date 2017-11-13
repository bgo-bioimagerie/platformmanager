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
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="quote/{{id_space}}">{{Quotes}}</a> 
                        </div>
                    </li>

                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="quoteuser/{{id_space}}">{{CreateExistingUserQuote}}</a>
                        </div>
                    </li>

                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="quotenew/{{id_space}}">{{CreateNewUserQuote}}</a>
                        </div>
                    </li>
                </ul>
            </ul>
        </div>
    </div>
</nav>