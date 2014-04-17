<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
$h = Loader::helper('concrete/dashboard');
echo $h->getDashboardPaneHeaderWrapper(t('Create new course'), false);
?>
<a href="<?php echo $this->action('view', $courseID) ?>">&laquo; <?php echo t('Courses'); ?></a>



<?php if (!$importSuccess) { ?>
    <h2>Create course</h2>
    <p><?php echo t('Create a new course based on a course template (zip-file).'); ?></p>

    <div class="row-fluid">
        <div class="span6"></h4>
            <form  method="post" enctype="multipart/form-data">
                <fieldset>
                    <legend><?php echo t('Use File Manager'); ?></legend>
                    <?php
                    $fObj = null;
                    $al = Loader::helper('concrete/asset_library');
                    ?>
                    <label><?php echo t('Select file:'); ?></label>
                    <?php echo $al->file('zip-file', 'fID', t('Choose or upload a file (zip)'), $fObj); ?>
                    <br />
                    <div style="text-align:right;">
                        <input type="submit" class="btn btn-submit" value="<?php echo t('Create course'); ?>"/>
                    </div>
                    <input type="hidden" name="action" value="fileManager" />
                    <?php
                    $token = Loader::helper('validation/token');
                    $token->output();
                    ?>
                </fieldset>
            </form>
        </div>
        <div class="span6">
            <fieldset>
                <legend><?php echo t('One-time upload'); ?></legend>
                <form method="post" enctype="multipart/form-data">
                    <?php $token->output(); ?>
                    <input type="hidden" name="action" value="upload" />
                    <label><?php echo t('Upload a file:'); ?></label>
                    <input type="file" name="file" />
                    <span class="help-block"><?php echo t('File will be deleted after course creation.'); ?></span>
                    <div style="text-align:right;">
                        <input type="submit" class="btn submit" value="<?php echo t('Create course'); ?>" />
                    </div>
            </fieldset>
            </form></div>
    </div>




<?php } else { ?>
    <p>
        <h3><?php echo t('Import log'); ?></h3>
        <textarea style="width:90%;" rows="10"><?php echo $log; ?></textarea>
    </p>
    <p>
        <a class="btn btn-primary" href="<?php echo $this->action('view', $courseID) ?>"><?php echo t('View course in dashboard'); ?> &raquo;</a>
    </p>
<?php } ?>

<?php
echo $h->getDashboardPaneFooterWrapper();
?>
