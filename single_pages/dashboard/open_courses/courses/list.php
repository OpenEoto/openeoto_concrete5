<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
$h = Loader::helper('concrete/dashboard');
echo $h->getDashboardPaneHeaderWrapper(t('Course administration'), false);
?>

<div>
    <?php if ($filterByCourseID !== NULL): ?>
        <h2><?php echo t('Course: ') . $filterByCourseID; ?></h2>
    <?php else: ?>
        <h2><?php echo t('Courses'); ?></h2>
    <?php endif; ?>
</div>

<br style="clear:both;" />

<div style="float:left;">
    <form method="POST">
        <?php
        $token = Loader::helper('validation/token');
        $token->output();
        ?>
        <input type="hidden" name="action" value="add_blank_course"/>
        <input type="text" name="title" placeholder="<?php echo t('Course title'); ?>" />
        <button type="submit" class="btn btn-primary"><?php echo t('Create blank course'); ?></button>
    </form>
</div>

<div style="float:right;">
    <a href="<?php echo $this->action('import') ?>" class="btn primary"><i class='icon-upload icon-white'></i> <?php echo t('Import course') ?></a>
</div>

<?php
$nh = Loader::helper('navigation');
if ($list->getTotal() > 0) {
    
    ?>

    <?php
    if ($filterByCourseID !== NULL) {
        echo "<p><a class='btn btn-warning' href='" . $this->action('view') . "'><i class='icon-filter icon-white'></i> " . t('Show all courses') . "</a>";
    }
    ?>

    <table border="0" class="ccm-results-list" cellspacing="0" cellpadding="0">
        <tr>

            <th class="<?php echo $list->getSearchResultsClass("ak_open_courses_course_is_published") ?>">
                <a href="<?php echo $list->getSortByURL('ak_open_courses_course_is_published', 'asc') ?>"><?php echo t('Published?'); ?></a> </th>
            <th>ID</th>
            <th class="<?php echo $list->getSearchResultsClass('cvName') ?>">
                <a href="<?php echo $list->getSortByURL('cvName', 'asc') ?>"><?= t('Title') ?></a>
            </th>
            <th class="<?php echo $list->getSearchResultsClass('cDateAdded') ?>">
                <a href="<?php echo $list->getSortByURL('cDateAdded', 'asc') ?>"><?= t('Date added') ?></a>
            </th>
            <th><?php echo t('Sessions'); ?></th>
            <th><?php echo t('Actions'); ?></th>
        </tr>
        <?php foreach ($results as $cobj) { ?>
            <tr>

                <td><?php echo $cobj->getAttribute("courses_course_is_published") ? t('Yes') : t('No'); ?></td>
                <td><?php echo $cobj->getCollectionID(); ?></td>
                <td><?php echo $cobj->getCollectionName() ?></td>
                <td><?php echo $cobj->getCollectionDateAdded() ?></td>
                <td><?php echo $cobj->getNumChildren(); ?></td>
                <td>
                    <a target="_blank" href="<?php echo $nh->getLinkToCollection($cobj) ?>"><?= t('Web view') ?></a> |
                    <a href="<?php echo $this->url('/dashboard/open_courses/courses/', 'teachers', $cobj->getCollectionID()); ?>"><?php echo t('Teachers'); ?></a> |
                    <a href="<?php echo $this->url('/dashboard/open_courses/courses', 'delete_check', $cobj->getCollectionID(), $cobj->getCollectionName()); ?>"><?php echo t('Delete') ?></a>
            </tr>
        <?php } ?>

    </table>
    <br/>

    <?php
    $list->displaySummary();
    echo "<br />";
    $list->displayPaging();
} // eo getTotal > 0
?>

<?php if ($list->getTotal() == 0) { ?>
    <br style="clear:both;" />
    <?php
    echo t('There is no course. Please create or import your first course here:');
} // eo getTotal == 0

print $h->getDashboardPaneFooterWrapper();
