<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
$h = Loader::helper('concrete/dashboard');
echo $h->getDashboardPaneHeaderWrapper(t('Course administration'), false);
?>
<div class = "alert-message block-message error">
<a class = "close" href = "<?php echo $this->action('view'); ?>">×</a>
<p><?php echo t('Do you really want to delete the course').' "'. $remove_name . '"?';
?></p>
<p><?php echo t('All data connected to this course will be removed.'); ?></p>
<div class="alert-actions">
    <a class="btn small" href="<?php echo BASE_URL . DIR_REL; ?>/index.php/dashboard/open_courses/courses/delete/<?php echo $remove_cid; ?>/<?php echo $remove_name; ?>/"><?php echo t('Yes, delete.'); ?></a> <a class="btn small" href="<?php echo $this->action('view'); ?>"><?php echo t('Cancel'); ?></a>
</div>
</div>

<?php
 print $h->getDashboardPaneFooterWrapper();