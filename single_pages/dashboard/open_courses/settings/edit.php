<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
$h = Loader::helper('concrete/dashboard');
echo $h->getDashboardPaneHeaderWrapper(t('Settings'), false);
?>
<a href="<?php echo $this->action('view') ?>">&laquo; <?php echo t('Settings'); ?></a>


<form class="form-horizontal" method="POST">
    <fieldset>
        <legend><?php echo t('Edit settings'); ?></legend>


        <div class="control-group">
            <label  class="control-label"><?php echo t('Course create mode'); ?></label>
            <div class="controls">
                <select name="OPEN_COURSES_SETTINGS_CREATE_MODE">
                    <option value="admin" <?php
                    if ($OPEN_COURSES_SETTINGS_CREATE_MODE == "" || $OPEN_COURSES_SETTINGS_CREATE_MODE == 'admin'): echo 'selected';
                    endif;
                    ?>><?php echo t('Admins only'); ?></option>
                    <option value="teacher" <?php
                    if ($OPEN_COURSES_SETTINGS_CREATE_MODE == 'teacher') {
                        echo 'selected';
                    }
                    ?>><?php echo t('Admins & Teachers'); ?></option>
                    <option value="everybody" <?php
                    if ($OPEN_COURSES_SETTINGS_CREATE_MODE == 'everybody') {
                        echo 'selected';
                    }
                    ?>><?php echo t('Learners, Teachers and Admins'); ?></option>
                </select>
                <span class="help-block" style="color:red;"><?php echo t('Warning: Please read the docs regarding security concerns if you want to give permission to learners and/or teachers to create/upload their own courses.'); ?></span>
            </div>
        </div>

        <div class="control-group">
            <label  class="control-label"><?php echo t('Allow script tags on import?'); ?></label>
            <div class="controls">
                <input type="checkbox" name="OPEN_COURSES_SETTINGS_IMPORT_ALLOW_SCRIPT_TAGS" value="1" <?php if ($OPEN_COURSES_SETTINGS_IMPORT_ALLOW_SCRIPT_TAGS) {
                                echo "checked";
                            } ?>>
                <span class="help-block" style="color:red;"><?php echo t('Warning: Please read the docs regarding security concerns if you enable this option.'); ?></span>
            </div>
        </div>

        
        <div class="control-group">
            <label  class="control-label"><?php echo t('Theme for (new) courses and sessions'); ?></label>
            <div class="controls">
                <input type="text" name="OPEN_COURSES_SETTINGS_THEME" value="<?php echo $OPEN_COURSES_SETTINGS_THEME; ?>" />
            </div>
        </div>


        <div class="control-group">
            <div class="controls">

                <input type="submit" class="btn btn-primary" value="<?php echo t('Save settings'); ?>" />
                <a href="<?php echo $this->action('view') ?>" class="btn"><?php echo t('Cancel'); ?> </a>
            </div>
    </fieldset>
<?php
$token = Loader::helper('validation/token');
$token->output();
?>

</form>


<?php
echo $h->getDashboardPaneFooterWrapper();
?>
