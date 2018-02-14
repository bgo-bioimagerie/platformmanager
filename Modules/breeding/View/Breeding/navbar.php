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
                            <!-- <a id="menu-button" href="brproductcategoryedit/{{id_space}}/0">+</a> -->
                        </div>
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brproducts/{{id_space}}">{{Products}}</a> 
                            <!-- <a id="menu-button" href="brproductedit/{{id_space}}/0">+</a> -->
                        </div>
                    </li>
                    <br/>
                    <li>
                        {{Glossary}}
                    </li>
                    <li>
                        <div class="inline pm-inline-div">
                            <a id="menu-button" href="brlossetypes/{{id_space}}">{{LosseTypes}}</a>
                            <!-- <a id="menu-button" href="brlossetypeedit/{{id_space}}/0">+</a> -->
                        </div>
                    </li>
                    
                </ul>
            </ul>
        </div>
    </div>
</nav>


