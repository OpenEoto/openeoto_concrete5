<?php defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<a href="<?php echo $this->action('view') . '?courseID=' . $courseID; ?>">&laquo; <?php echo t('Course dashboard'); ?></a>

<h2 class="page-header"><?php echo $courseTitle; ?></h2>

<div class="open-courses-system-messages">
    <?php
    if (isset($errors)) {
        foreach ($errors as $e) {
            echo "<div class='error'>" . $e . "</div>";
        }
    }
    ?>
</div>
<?php
$vt = Loader::helper('validation/token');
?>


<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title"><?php echo t('Edit session'); ?></h3>
            </div>
            <div class="box-body">
                <form method="post" role="form" enctype="multipart/form-data">
                    <div class="form-group">      
                        <label><?php echo t('Title'); ?>  </label>
                        <input class="form-control" type="text" name="title" value="<?php echo $title; ?>" />

                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="isPublished" value="1"<?php
                            if ($isPublished): echo 'checked';
                            endif;
                            ?> /> <?php echo t('Is published?'); ?>
                        </label>
                    </div>

                    <?php
                    // deactivated by now, maybe used later...
                    /*
                      <p>
                      <label><?php echo t('Learner completion requires teacher approval?'); ?></label>
                      <select name="completionApprovalNeeded">
                      <?php foreach ($completionApprovalNeededValues as $v) { ?>
                      <option value='<?php echo $v->value; ?>' <?php
                      if ($v->value == $completionApprovalNeeded): echo 'selected';
                      endif;
                      ?>><?php echo t($v->value); ?></option>
                      <?php } ?>
                      </select>
                      </p>
                     * 
                     */
                    ?>
            </div>
            <div class="box-footer">
                <?php print $vt->output(); ?>
                <button type="submit" class="btn btn-primary"><?php echo t('Save changes'); ?></button>
            </div>
            </form>
        </div>
    </div>
    <div class="col-md-6">

    </div>
</div>


