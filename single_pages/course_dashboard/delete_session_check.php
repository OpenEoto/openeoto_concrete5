<?php defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<a href="<?php echo $this->action('view') . '?courseID=' . $courseID; ?>">&laquo; <?php echo t('Course dashboard'); ?></a>

<form method="POST">
    <h3><?php echo t('Delete session?'); ?></h3>

    <p><?php echo t('Do you really want to delete the following session? All data will be lost. This can not be undone!'); ?></p> 

    <button class="btn btn-danger" type="submit"><?php echo t('Yes, delete'); ?></button>
</form>