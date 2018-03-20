<div class="col-xs-12" style="border: none; margin-top: 7px; padding-right: 0px; padding-left: 0px;">
    <div class="col-xs-12" style="height: 50px; padding-top: 15px; background-color:{{bgcolor}}; border-bottom: 1px solid #fff;">
        <a  style="background-color:{{bgcolor}}; color: #fff;" href=""> {{title}} 
            <span style="color: #fff; font-size:16px; float:right;" class=" hidden-xs showopacity glyphicon {{glyphicon}}"></span>
        </a>
    </div>

    <div class="col-xs-12 pm-inline-div" style="background-color:{{bgcolor}};">
        <a style="color: #fff" id="menu-button" href="ecbelongings/{{id_space}}">{{Belongings}}</a>
        <a  style="color: #fff" href="ecbelongingsedit/{{id_space}}/0">+</a>   
    </div>
    <div class="col-xs-12 pm-inline-div" style="background-color:{{bgcolor}};">
        <a  style="color: #fff" id="menu-button" href="ecunits/{{id_space}}">{{Units}}</a>
        <a  style="color: #fff" href="ecunitsedit/{{id_space}}/0">+</a>   
    </div>

    <div class="col-xs-12" style="background-color:{{bgcolor}};">
        <br/>

        {{Users}}
    </div>
    <div class="col-xs-12 pm-inline-div" style="background-color:{{bgcolor}};">
        <a style="color: #fff" href="ecusersedit/{{id_space}}/0">{{Neww}}</a>   
    </div>
    <div class="col-xs-12 pm-inline-div" style="background-color:{{bgcolor}};">
        <a style="color: #fff" id="menu-button" href="ecactiveusers/{{id_space}}">{{Active}}</a>
    </div>
    <div class="col-xs-12 pm-inline-div" style="background-color:{{bgcolor}};">
        <a style="color: #fff" id="menu-button" href="ecunactiveusers/{{id_space}}">{{Unactive}}</a>
    </div>
    <div class="col-xs-12 pm-inline-div" style="background-color:{{bgcolor}};">
        <br/>
        {{Export}}
    </div>
    <div class="col-xs-12 pm-inline-div" style="background-color:{{bgcolor}};">
        <a style="color: #fff" href="ecexportresponsible/{{id_space}}">{{Responsible}}</a> 
        <a style="color: #fff" href="ecexportall/{{id_space}}">{{ExportAll}}</a>    
    </div>

</div>