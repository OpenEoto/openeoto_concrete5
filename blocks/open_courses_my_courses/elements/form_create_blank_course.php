<?php defined('C5_EXECUTE') or die("Access Denied."); ?>                          
<form method="POST" action="<?php echo $this->action('create_blank'); ?>">
    <div class="form-group">
        <label><?php echo t('Title:'); ?></label>
        <input type="text" class="form-control" name="title" placeholder="<?php echo t('Title'); ?>">

    </div>
    <button class="btn" type="submit"><?php echo t('Create course'); ?></button>
</form>
