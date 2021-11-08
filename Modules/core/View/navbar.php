<?php 
$modelConfig = new CoreConfig();
$menuUrl = $modelConfig->getParam("menuUrl");
$margin = "";
if ($menuUrl != ""){
    $margin = "style=\"margin-top: 50px;\"";
}

?>
<!-- Fixed navbar -->
<nav class="navbar navbar-inverse navbar-fixed-top" <?php echo $margin ?> role="navigation" style="z-index: 999;">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
		<div id="navbar" class="collapse navbar-collapse">
			<ul class="nav navbar-nav">
				<li><a href="coretiles"><span class="glyphicon glyphicon-th"></span></a></li>
                            <?php
							if(count($toolMenu) > 5) {
							?>
								<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo  CoreTranslator::Menus($lang) ?> <span class="caret"></span></a>
								<ul class="dropdown-menu" role="menu">
								  <?php 
									foreach ($toolMenu as $tool) {
										echo '<li><a href="coretiles/1/'.$tool["id"].'" > '.$tool["name"].'</a></li>';
									}
								  ?>
								</ul>
							</li>
							<?php
							} else {
                            for($i = 0 ; $i < count($toolMenu) ; $i++){
                                ?>
				<li>
                                    <a href="coretiles/1/<?php echo $toolMenu[$i]["id"] ?>" > <?php echo $toolMenu[$i]["name"] ?></a>
				</li>
                                <?php
							}
                            }
                            ?>
				
				<?php 
				if ($toolAdmin){
				?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo  CoreTranslator::Admin($lang) ?> <span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu">
				      <?php 
        				foreach ($toolAdmin as $tool) {
        					$key = $tool['link'];
        					$value = $tool['name'];
        					echo "<li><a href= $key > $value </a></li>";
        				}
        			  ?>
					</ul>
				</li>
				<?php }?>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<?php if($impersonate!=null) { ?><li><a href="/corespaceaccess/0/unimpersonate"><button class="btn btn-danger">Log back to <?php echo $impersonate; ?></button></a></li><?php } ?>
				<?php if(isset($_SESSION["login"])) { ?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><img onerror="this.style.display='none'" alt="avatar" src="<?php echo "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $_SESSION['email'] ) ) ) . "?s=20"; ?>"/> <?php echo  $userName ?> <span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu">
        				<li><a href=coremyaccount > <?php echo  CoreTranslator::My_Account($lang) ?> </a></li>
        				<li><a href=coresettings > <?php echo  CoreTranslator::Settings($lang) ?> </a></li>
        				<li class="divider"></li>
        				<li><a href=corelogout> <?php echo  CoreTranslator::logout($lang) ?> </a></li>
					</ul>
				</li>
				<?php } else { ?>
					<li><a href="/coreconnection">Login</a></li>
					<?php if(intval(Configuration::get('allow_registration', 0)) == 1) { ?><li><a href="/corecreateaccount"><?php echo CoreTranslator::CreateAccount($lang) ?></a></li><?php } ?>
				<?php }?>
			</ul>
		</div><!--/.nav-collapse -->
	</div>
</nav>
<!-- Bootstrap core JavaScript -->
<script src="externals/jquery-1.11.1.js"></script>
<script src="externals/bootstrap/js/bootstrap.min.js"></script> 
