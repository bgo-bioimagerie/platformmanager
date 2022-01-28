<!-- Carousel
================================================== -->
<div id="myCarousel" class="carousel slide" data-ride="carousel" style="width: 100%;">
    <!-- Indicators -->
    <ol class="carousel-indicators">
        <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#myCarousel" data-slide-to="1" ></li>
        <li data-target="#myCarousel" data-slide-to="2" ></li>
    </ol>
    <div class="carousel-inner" role="listbox">
        <div class="item active">
            <img class="first-slide" src="<?php echo $urlCarousel1 ?>" alt="First slide">
            <div class="container">
                <div class="carousel-caption col-4 col-offset-4">
                </div>
            </div>
        </div>
        <div class="item">
            <img class="second-slide" src="<?php echo $urlCarousel2 ?>" alt="Second slide">
            <div class="container">
                <div class="carousel-caption col-4 col-offset-4">
                </div>
            </div>
        </div>
        <div class="item">
            <img class="third-slide" src="<?php echo $urlCarousel3 ?>" alt="Third slide">
            <div class="container">
                <div class="carousel-caption col-4 col-offset-4">
                </div>
            </div>
        </div>
    </div>
    <a class="left carousel-control" href="<?= $_SERVER['REQUEST_URI'] ?>#myCarousel" role="button" data-slide="prev">
        <span class="bi-chevron-left" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="right carousel-control" href="<?= $_SERVER['REQUEST_URI'] ?>#myCarousel" role="button" data-slide="next">
        <span class="bi-chevron-right" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div><!-- /.carousel -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="./Modules/core/Theme/caroussel/jquery.min.js"></script>
    <script src="./Modules/core/Theme/caroussel/bootstrap.min.js"></script>
    <!-- Just to make our placeholder images work. Don't actually copy the next line! -->
    <script src="./Modules/core/Theme/caroussel/holder.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="./Modules/core/Theme/caroussel/ie10-viewport-bug-workaround.js"></script>
