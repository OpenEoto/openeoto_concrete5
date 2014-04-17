<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
$h = Loader::helper('concrete/dashboard');
echo $h->getDashboardPaneHeaderWrapper(t('Course administration'), false);
?>

<a href="<?php echo $this->action('view',$courseID) ?>">&laquo; <?php echo t('Courses'); ?></a>

<h2><?php echo t('Course teachers'); ?></h2>

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
                <td><a href="<?php echo $this->action('remove_teacher',$courseID,$userInfo->getUserID()) ?>"><?php echo t('Remove');?></a></td>
            </tr>
        <?php } ?>

    </table>

    <?php
}
?>


    <h3><?php echo t('Add teacher'); ?></h3>
    <form method="post" action="<?php echo $this->action('add_teacher',$courseID) ?>" enctype="multipart/form-data">
        <?php
        $token = Loader::helper('validation/token');
        $token->output();
        $uh = Loader::helper('form/user_selector');
        echo $uh->selectUser('userID', $uID = false, $javascriptFunc = 'ccm_triggerSelectUser');
        ?>
        <input class="btn" type="submit" value="<?php echo t('Add user'); ?>" />
    </form>






    <?php
    print $h->getDashboardPaneFooterWrapper();
    