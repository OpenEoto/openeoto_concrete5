<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
$h = Loader::helper('concrete/dashboard');
echo $h->getDashboardPaneHeaderWrapper(t('Set up permissions'), false);
?>
<a href="<?php echo $this->action('view') ?>">&laquo; <?php echo t('Settings'); ?></a>


<form class="form-horizontal" method="POST">
    <fieldset>
        <legend><?php echo t('Setup permissions'); ?></legend>

        <p  style="color:red;"><?php echo t('Warning: This action will overwrite pre-existing advanced permissions for the dashboard system pages & child pages! Are you sure?'); ?></p>

        <input type="submit" class="btn-primary btn" value="<?php echo t('Yes, setup permissions!'); ?>">
    </fieldset>
    <?php
    $token = Loader::helper('validation/token');
    $token->output();
    ?>
</form>

<?php
echo $h->getDashboardPaneFooterWrapper();
?>