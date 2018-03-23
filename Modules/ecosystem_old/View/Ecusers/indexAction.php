<?php include 'Modules/ecosystem/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12 pm-table">
    <div class="col-md-12" style="height:7px;">
    </div>
    <div class="text-center">
        <div class="btn-group btn-group-sm">
            <button class="btn btn-default <?php if ($letter == "All") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/All/<?php echo $active ?>';"><?php echo EcosystemTranslator::All($lang) ?></button>
            <button class="btn btn-default <?php if ($letter == "A") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/A/<?php echo $active ?>';">A</button>
            <button class="btn btn-default <?php if ($letter == "B") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/B/<?php echo $active ?>';">B</button>
            <button class="btn btn-default <?php if ($letter == "C") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/C/<?php echo $active ?>';">C</button>
            <button class="btn btn-default <?php if ($letter == "D") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/D/<?php echo $active ?>';">D</button>
            <button class="btn btn-default <?php if ($letter == "E") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/E/<?php echo $active ?>';">E</button>
            <button class="btn btn-default <?php if ($letter == "F") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/F/<?php echo $active ?>';">F</button>
            <button class="btn btn-default <?php if ($letter == "G") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/G/<?php echo $active ?>';">G</button>
            <button class="btn btn-default <?php if ($letter == "H") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/H/<?php echo $active ?>';">H</button>
            <button class="btn btn-default <?php if ($letter == "I") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/I/<?php echo $active ?>';">I</button>
            <button class="btn btn-default <?php if ($letter == "J") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/J/<?php echo $active ?>';">J</button>
            <button class="btn btn-default <?php if ($letter == "K") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/K/<?php echo $active ?>';">K</button>
            <button class="btn btn-default <?php if ($letter == "L") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/L/<?php echo $active ?>';">L</button>
            <button class="btn btn-default <?php if ($letter == "M") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/M/<?php echo $active ?>';">M</button>
            <button class="btn btn-default <?php if ($letter == "N") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/N/<?php echo $active ?>';">N</button>
            <button class="btn btn-default <?php if ($letter == "O") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/O/<?php echo $active ?>';">O</button>
            <button class="btn btn-default <?php if ($letter == "P") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/P/<?php echo $active ?>';">P</button>
            <button class="btn btn-default <?php if ($letter == "Q") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/Q/<?php echo $active ?>';">Q</button>
            <button class="btn btn-default <?php if ($letter == "R") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/R/<?php echo $active ?>';">R</button>
            <button class="btn btn-default <?php if ($letter == "S") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/S/<?php echo $active ?>';">S</button>
            <button class="btn btn-default <?php if ($letter == "T") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/T/<?php echo $active ?>';">T</button>
            <button class="btn btn-default <?php if ($letter == "U") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/U/<?php echo $active ?>';">U</button>
            <button class="btn btn-default <?php if ($letter == "V") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/V/<?php echo $active ?>';">V</button>
            <button class="btn btn-default <?php if ($letter == "W") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/W/<?php echo $active ?>';">W</button>
            <button class="btn btn-default <?php if ($letter == "X") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/X/<?php echo $active ?>';">X</button>
            <button class="btn btn-default <?php if ($letter == "Y") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/Y/<?php echo $active ?>';">Y</button>
            <button class="btn btn-default <?php if ($letter == "Z") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/Z/<?php echo $active ?>';">Z</button>
        </div>

    </div>
    <div class="col-md-12" style="height: 7px;">
    </div>
    <div class="col-md-12">
<?php echo $tableHtml ?>
    </div>
</div>
<?php
endblock();
