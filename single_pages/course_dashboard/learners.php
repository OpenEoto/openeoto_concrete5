<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
?>
<a href="<?php echo $this->action('view').'?courseID='.$courseID; ?>">&laquo; <?php echo t('Course dashboard'); ?></a>

<h2 class="page-header"><?php echo $courseTitle; ?></h2>

<h3><?php echo t('Learner management'); ?></h3>

<div class="open-courses-system-message">
    <p><?php echo $message; ?></p>
</div>

<?php
$vt = Loader::helper('validation/token');
?>
<h4><?php echo t('Add registered user'); ?></h4>
<form method="post" enctype="multipart/form-data">
    <p>        
        <label><?php echo t('Email'); ?></label>
        <input type="text" name="email" />
        <?php echo t('or'); ?>
        <label><?php echo t('Username'); ?></label>
        <input type="text" name="username" />
        <input type="hidden" name="add_type" value="registered_user" />
        <input class="btn" type="submit" value="<?php echo t('Add'); ?>" />
        <?php print $vt->output(); ?>
    </p>
</form>

<h4><?php echo t('Invite user'); ?></h4>
<form method="post" enctype="multipart/form-data">
    <p><?php echo t('You can invite unregistered users via this code: ').$courseCode." ".t('(See Open Courses Manual for more information)'); ?></p>
</form>

<h4><?php echo t('Joined learners'); ?></h4>

<?php
$nh = Loader::helper('navigation');
if ($list->getTotal() > 0) {
    $list->displaySummary();
    ?>
    <table border="0" class="ccm-results-list" cellspacing="0" cellpadding="0">
        <tr>
            <th><?php echo t('Username'); ?></th>
            <th><?php echo t('E-Mail'); ?></th>
            <th><?php echo t('Actions'); ?></th>
        </tr>
        <?php foreach ($results as $userInfo) { ?>
            <tr>
                <td><?php echo $userInfo->getUserName(); ?></td>
                <td><?php echo $userInfo->getUserEmail(); ?></td>
                <td><a href="<?php echo $this->action('remove_learner',$userInfo->getUserID(), "?courseID=".$courseID ) ?>"><?php echo t('Remove'); ?></a></td>
            </tr>
        <?php } ?>

    </table>

    <?php
} // 2DO: pagination?
?>


