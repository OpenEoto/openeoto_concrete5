<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<form method = "POST" action="<?php echo $this->action('upload_course'); ?>" enctype="multipart/form-data">
<div class="form-group">
    <label><?php echo t('Course file (.zip):'); ?></label>
    <input type="file" name="file">
</div>
<button class="btn" type="submit" value="upload_course"><?php echo t('Create course'); ?></button>
</form>
