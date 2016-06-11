<?php include 'Modules/ecosystem/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="container">
    <div class="col-md-10 col-md-offset-1">
        <form role="form" class="form-horizontal" action="ecuserschangepwdq" method="post">
            <div class="page-header">
                <h1>
                    <?php echo CoreTranslator::Change_password($lang) ?>
                    <br> <small> for user</small>
                </h1>
                <div class="form-group">
                    <input class="form-control" id="login" type="hidden" name="login" value=<?php echo $user['login'] ?> readonly />
                    <div class="col-xs-4">
                        <input class="form-control" id="firstname" type="text" name="firstname" value=<?php echo $user['firstname'] ?> readonly />
                    </div>
                    <div class="col-xs-4">
                        <input class="form-control" id="name" type="text" name="name" value=<?php echo $user['name'] ?> readonly />
                    </div>
                    <label for="pwd" class="control-label col-xs-2">ID:</label>
                    <div class="col-xs-2">
                        <input class="form-control" id="id" type="text" name="id" value=<?php echo $user['id'] ?> readonly />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label for="pwd" class="control-label col-xs-2"><?php echo CoreTranslator::Password($lang) ?></label>
                    <div class="col-xs-4">
                        <input type="password" class="form-control" id="pwd" name="pwd" placeholder="Password">
                    </div>
                    <div class="form-group">
                    </div>
                    <label for="pwdc" class="control-label col-xs-2"><?php echo CoreTranslator::Confirm($lang) ?> </label>
                    <div class="col-xs-4">
                        <input type="password" class="form-control" id="pwdc" name="pwdc" placeholder="Password">
                    </div>
                </div>
                <br>
                <div class="col-xs-4 col-xs-offset-8" id="button-div">
                    <input type="submit" class="btn btn-primary" value="<?php echo CoreTranslator::Save($lang) ?>" />
                    <button type="button" onclick="location.href = 'ecusers'" class="btn btn-default"><?php echo CoreTranslator::Cancel($lang) ?></button>
                </div>
            </div>
        </form>

    </div>
</div>
<?php
endblock();
