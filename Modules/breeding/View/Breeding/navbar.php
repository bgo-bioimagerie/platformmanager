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
                        {{Sales}}
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brsalenew/{{id_space}}">{{NewSale}}</a> 
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brsalesinprigress/{{id_space}}">{{SalesInProgress}}</a> 
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brsalessent/{{id_space}}">{{SalesSent}}</a> 
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brsalescanceled/{{id_space}}">{{SalesCanceled}}</a> 
                        </div>
                    </li>
                    <br/>
                    <li>
                        {{Batchs}}
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brbatchnew/{{id_space}}">{{NewBatch}}</a> 
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brbatchsinprogress/{{id_space}}">{{BatchsInProgress}}</a> 
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brbatchsarchives/{{id_space}}">{{BatchsArchives}}</a> 
                        </div>
                    </li>
                    <br/>
                    <li>
                        {{Products}}
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brproductcategories/{{id_space}}">{{CategoriesProduct}}</a> 
                            <a id="menu-button" href="brproductcategoryedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brproducts/{{id_space}}">{{Products}}</a> 
                            <a id="menu-button" href="brproductedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brprices/{{id_space}}">{{Prices}}</a> 
                        </div>
                    </li>
                    
                    <br/>
                    <li>
                        {{UsersInstitutions}}
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brclients/{{id_space}}">{{Clients}}</a>
                            <a id="menu-button" href="brclientedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brpricings/{{id_space}}">{{Pricings}}</a>
                            <a id="menu-button" href="brpricingedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brcompany/{{id_space}}">{{CompanyInfo}}</a>
                        </div>
                    </li>
                    <br>
                    <li>
                        {{Glossary}}
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brcontacttypes/{{id_space}}">{{ContactTypes}}</a>
                            <a id="menu-button" href="brcontacttypeedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brdeliveries/{{id_space}}">{{Delivery}}</a>
                            <a id="menu-button" href="brdeliveryedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brlossetypes/{{id_space}}">{{LosseTypes}}</a>
                            <a id="menu-button" href="brlossetypeedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    
                </ul>
            </ul>
        </div>
    </div>
</nav>


