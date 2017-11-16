
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
                        Listing
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="anticorps/{{id_space}}">Anticorps</a>
                            <a href="anticorpsedit/{{id_space}}/0">+</a>   
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="protocols/{{id_space}}/id">Protocoles</a>
                            <a href="protocolsedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <br/>
                    <li>
                        Référence
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="sources/{{id_space}}">Sources</a>
                            <a href="sourcesedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="isotypes/{{id_space}}">Isotypes</a>
                            <a href="isotypesedit/{{id_space}}/0">+</a>
                        </div>
                    </li>

                    <br/>
                    <li>
                        Tissus
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="especes/{{id_space}}">Espèces</a>
                            <a href="especesedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="organes/{{id_space}}">Organes</a>
                            <a href="organesedit/{{id_space}}/0">+</a>
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="prelevements/{{id_space}}">Prélèvements</a>
                            <a href="prelevementsedit/{{id_space}}/0">+</a>
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="status/{{id_space}}">Status</a>
                            <a href="statusedit/{{id_space}}/0">+</a>
                        </div>
                    </li>

                    <br/>
                    <li>
                        Détails Proto
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="kit/{{id_space}}">KIT</a>
                            <a href="kitedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="proto/{{id_space}}">Proto</a>
                            <a href="protoedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="fixative/{{id_space}}">Fixative</a>
                            <a href="fixativeedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="option/{{id_space}}">Option</a>
                            <a href="optionedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="enzymes/{{id_space}}">Enzyme</a>
                            <a href="enzymesedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="dem/{{id_space}}">Dém</a>
                            <a href="demedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="aciinc/{{id_space}}">AcI Inc</a>
                            <a href="aciincedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="linker/{{id_space}}">Linker</a>
                            <a href="linkeredit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="inc/{{id_space}}">Linker Inc</a>
                            <a href="incedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="acii/{{id_space}}">acII</a>
                            <a href="aciiedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="application/{{id_space}}">Application</a>
                            <a href="applicationedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                    <li>
                        <div class="pm-inline-div">
                            <a id="menu-button" href="staining/{{id_space}}">Marquage</a>
                            <a href="stainingedit/{{id_space}}/0">+</a> 
                        </div>
                    </li>
                </ul>
            </ul>
        </div>
    </div>
</nav>