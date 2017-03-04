<?php
require_once 'Modules/core/Model/CoreTranslator.php';
?>

<div class="col-md-12">
    <h3><?php echo CoreTranslator::Menus($lang) ?></h3>
    <button onclick="location.href = 'coremenus/'" class="btn btn-link" id="navlink"><?php echo CoreTranslator::Menus($lang) ?></button>
    <br/>
    <button onclick="location.href = 'coremenusitems'" class="btn btn-link" id="navlink"><?php echo CoreTranslator::Items($lang) ?> </button>
    <button onclick="location.href = 'coremenusitemedit/0'" class="btn btn-link" id="navlink">+</button>


</div>
