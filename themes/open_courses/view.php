<?php
defined('C5_EXECUTE') or die("Access Denied.");
$this->inc('elements/header.php');
?>

<aside class="left-side sidebar-offcanvas" id="sidebar">
    <?php $this->inc('elements/sidebar.php'); ?>
    <?php
    // additional area for sidebar
    $as = new Area('Sidebar');
    $as->display($c);
    ?>
</aside>

<aside class="right-side">                
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <?php echo $c->getCollectionName(); ?>
        </h1>
        
        <?php
        /*$nav = BlockType::getByHandle("autonav");
        $nav->controller->orderBy = 'display_asc';
        $nav->controller->displayPages = 'top';
        $nav->controller->displaySubPages = 'relevant_breadcrumb';
        $nav->controller->displaySubPageLevels = 'enough';
        $nav->render('templates/breadcrumb_open_courses');*/
        ?>
     
    </section>

    <!-- Main content -->
    <section class="content">
        <?php
        print $innerContent;
        ?>
    </section><!-- /.content -->
</aside><!-- /.right-side -->

<?php $this->inc('elements/footer.php'); ?>