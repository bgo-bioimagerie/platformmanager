<?php
include_once 'Modules/core/View/layout.php';
require_once 'Modules/core/Model/CoreTranslator.php';
?>

<!-- body -->     
<?php startblock('content') ?>

<div class=" container">
    <div class="row">
        <form method="POST" action="coretilesselfjoinspace/<?php echo $idSpace ?>">
            <input type="hidden" name="formid" value="coretilesselfjoinspace">
            <input type="hidden" name="space" value="<?php echo $idSpace; ?>">
            <div class="form-group">
                <label class="form-label col-xs-4" for="comment"><?php echo CoreTranslator::JoinWhy($lang) ?>*</label>
                <div class="col-xs-8">
                    <textarea class="form-control" id="comment" name="comment" required></textarea>
                </div>
            </div>
            <?php if (isset($context['currentSpace']['termsofuse']) && $context['currentSpace']['termsofuse']) { ?>
            <div class="form-group mb-3">
                <label class="form-label col-xs-4" for="agree">I agree with terms of use* : <a target="_blank" rel="nofollow noopener noreferrer" href="<?php echo $context['currentSpace']['termsofuse'] ?>">(read policy)</a></label>
                <div class="col-xs-8">
                    <input class="form-checkbox" type="checkbox" name="agree" id="agree" value="I agree with the policy" required>
                </div>
            </div>
            <?php }  ?>
            <div class="form-group">
                <div class="col-xs-12">
                    <button type="submit" class="btn btn-primary"><?php echo CoreTranslator::RequestJoin(false, $lang); ?></button>
                    <a href="coretiles"><button type="button" class="btn btn-secondary"><?php echo CoreTranslator::Cancel($lang) ?></button></a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
endblock();
