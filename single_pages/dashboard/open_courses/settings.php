<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
$h = Loader::helper('concrete/dashboard');
echo $h->getDashboardPaneHeaderWrapper(t('Settings'), false);
?>

<div class="form-horizontal">
    <fieldset>
        <legend><?php echo t('Settings'); ?></legend>

        <div class="control-group">
            <label  class="control-label"><?php echo t('Course create mode'); ?></label>
            <div class="controls">
                <?php
                switch ($OPEN_COURSES_SETTINGS_CREATE_MODE) {
                    case 'everybody':
                        echo t('All registered users can create courses.');
                        break;
                    case 'teacher':
                        echo t('Teachers and Admins can create courses.');
                        break;
                    default:
                        echo t('Only Admins can create courses.');
                        break;
                }
                ?>
            </div>
        </div>
        <div class="control-group">
            <label  class="control-label"><?php echo t('Allow script tags on import?'); ?></label>
            <div class="controls">
                <?php
                if ($OPEN_COURSES_SETTINGS_IMPORT_ALLOW_SCRIPT_TAGS !== TRUE) {
                    echo t('No');
                } else {
                    echo t('Yes');
                }
                ?>
            </div>
        </div>


        <div class="control-group">
            <label  class="control-label"><?php echo t('Theme handle for (new) courses and sessions'); ?></label>
            <div class="controls">
                <?php
                echo $OPEN_COURSES_SETTINGS_THEME;
                ?>
            </div>
        </div>

        <div class="control-group">
            <div class="controls">

                <a href="<?php echo $this->action('edit'); ?>" class="btn"><?php echo t('Edit settings'); ?></a>
            </div>
        </div>
</div>

<div class="form-horizontal">
    <fieldset>
        <legend><?php echo t('Setup initial permissions'); ?></legend>

        <p><?php echo t('You can set here (most of the) the dashboard page permissions automatically after installation.'); ?></p>
        <a href="<?php echo $this->action('setup_permissions'); ?>" class="btn btn-primary"><?php echo t('Setup permissions'); ?></a>
</div>

<?php
echo $h->getDashboardPaneFooterWrapper();
?>
