<?php require_once 'Framework/ti.php' ?>
<?php include 'Modules/layout.php' ?>

<!-- header -->
<?php startblock('title') ?>
Platform-Manager
<?php endblock() ?>
<?php startblock('stylesheet') ?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php
if (getenv('PFM_MODE') != 'dev') {
    echo "<meta http-equiv=\"Content-Security-Policy\" content=\"default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'\">\n";
} else {
    echo "<meta http-equiv=\"Content-Security-Policy-Report-Only\" content=\"default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'\">\n";
}
?>
<meta http-equiv="X-XSS-Protection" content="1; mode=block">
<meta http-equiv="X-Content-Type-Options" content="nosniff">

<!-- Bootstrap core CSS -->
<link href="externals/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<?php endblock() ?>

<!-- body -->
<?php startblock('content') ?>

<div class="col-md-12" style="background-color: #fff; height:100%">
    <div class="row">
        <!-- Message -->
        <div class="col-sm-10 col-sm-offset-1 text-center">
             <?php
        if (isset($_SESSION["message"])) {
            if (substr($_SESSION["message"], 0, 3) === "Err") {
                ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION["message"] ?>
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION["message"] ?>
                </div>
                <?php
            }
            unset($_SESSION["message"]);
        }
        ?>
        </div>

         <!-- Form -->
        <div class="col-sm-10 col-sm-offset-1 text-center">
            should see tickets
        </div>
        <div id="helpdeskapp">
        {{message}}
        </div>

    </div>
</div>
<script>
var app = new Vue({
  el: '#helpdeskapp',
  data: {
    message: 'Hello Vue!'
  }
})
</script>
<?php
endblock();

