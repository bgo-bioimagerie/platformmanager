<?php include 'Modules/antibodies/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-content">
    
    <div class="col-12 pm-form-short">
        <?php echo $form ?>
        <a class="btn btn-danger m-3" href="antibodydelete/<?php echo $id_space ?>/<?php echo $id ?>"><?php echo CoreTranslator::Delete($lang) ?></a>
    </div>
    <div class="col-12">
        <div class="col-12 pm-table-short">
            <?php echo $tissusTable ?>
            <a onclick="addTissuesForm()" class="btn btn-primary m-3" id="addtissusbutton"><?php echo AntibodiesTranslator::addTissus($lang) ?></a>
        </div>
        
        <div class="col-12 pm-table-short">
            <?php echo $formCatalog ?>
        </div>
        
        <div class="col-12 pm-table-short">
            <?php echo $ownersTable ?>
            <a onclick="addOwnerForm()" class="btn btn-primary m-3" id="addownerbutton"><?php echo AntibodiesTranslator::addOwner($lang) ?></a>
        </div>
    </div>
    
</div>

<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->


<div id="tissuspopup_box" class="modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php echo AntibodiesTranslator::Tissus($lang) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <?php echo $formtissus ?>
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>

<div id="ownerpopup_box" class="modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php echo AntibodiesTranslator::Owner($lang) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <?php echo $formowner ?>
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>


<script>
    function editentry(id) {
        var arrayid = id.split("_");
                //alert("add note clicked " + arrayid[1]);
        if(arrayid[0] == 'edittissus') {
            editTissuesForm(<?php echo $id_space ?>, arrayid[1]);
        } else if(arrayid[0] == 'editowner') {
            editOwnerForm(<?php echo $id_space ?>, arrayid[1]);
        }
    }

    function addTissuesForm() {
        $('#id').val(0);
        $('#id_antibody').val(<?php echo $id ?>);
        $('#ref_protocol').val(0);
        $('#dilution').val("");
        $('#comment').val("");
        $('#espece').val(0);
        $('#organe').val(0);
        $('#status').val(0);
        $('#ref_bloc').val("");
        $('#prelevement').val(0);

        let myModal = new bootstrap.Modal(document.getElementById('tissuspopup_box'))
        myModal.show();
    };

    function editTissuesForm(id_space, id_tissus) {
            $.post(
                'apiantibodytissus/' + id_space + '/' + id_tissus,
                {},
                function (data) {
                    $('#id').val(data.id);
                    $('#id_antibody').val(data.id_anticorps);
                    $('#ref_protocol').val(data.ref_protocol);
                    $('#dilution').val(data.dilution);
                    $('#comment').val(data.comment);
                    $('#espece').val(data.espece);
                    $('#organe').val(data.organe);
                    $('#status').val(data.status);
                    $('#ref_bloc').val(data.ref_bloc);
                    $('#prelevement').val(data.prelevement);

                    let myModal = new bootstrap.Modal(document.getElementById('tissuspopup_box'))
                    myModal.show();
                },
                'json'
            );

    }

    function addOwnerForm() {
        $('#owner_id').val(0);
        $('#owner_id_anticorps').val(<?php echo $id ?>);
        $('#owner_id_user').val("");

        $('#owner_disponible').val("");
        $('#owner_date_recept').val("");
        $('#owner_no_dossier').val("");

        let myModal = new bootstrap.Modal(document.getElementById('ownerpopup_box'))
        myModal.show();
    }

    function editOwnerForm(id_space, id_owner) {
        $.post(
            'apiantibodyowner/' + id_space + '/' + id_owner,
            {},
            function (data) {
                $('#owner_id').val(data.id);
                $('#owner_id_anticorps').val(data.id_anticorps);
                $('#owner_id_user').val(data.id_utilisateur);

                $('#owner_disponible').val(data.disponible);
                $('#owner_date_recept').val(data.date_recept);
                $('#owner_no_dossier').val(data.no_dossier);

                let myModal = new bootstrap.Modal(document.getElementById('ownerpopup_box'))
                myModal.show();
            },
            'json'
            );
        }

</script>
<?php endblock(); ?>
