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
            <?php echo t('Dashboard'); ?>
        </h1>
        
        <?php
        $nav = BlockType::getByHandle("autonav");
        $nav->controller->orderBy = 'display_asc';
        $nav->controller->displayPages = 'top';
        $nav->controller->displaySubPages = 'relevant_breadcrumb';
        $nav->controller->displaySubPageLevels = 'enough';
        $nav->render('templates/breadcrumb_open_courses');
        ?>
     
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                <?php
                $a = new Area('Main');
                $a->display($c);
                ?>
            </div>
        </div>

        <div class="row">
            <!-- left column -->
            <div class="col-md-6">
                <?php
                $a = new Area('2-Left-Column');
                $a->display($c);
                ?>
            </div>
            <!-- right column -->
            <div class="col-md-6">
                <?php
                $a = new Area('2-Right-Column');
                $a->display($c);
                ?>
            </div>

        </div>
        
        <div class="row">
            <!-- left column -->
            <div class="col-md-4">
                <?php
                $a = new Area('3-Left-Column');
                $a->display($c);
                ?>
            </div>
            <!-- right column -->
            <div class="col-md-4">
                <?php
                $a = new Area('3-Middle-Column');
                $a->display($c);
                ?>
            </div>
            
            <div class="col-md-4">
                <?php
                $a = new Area('3-Right-Column');
                $a->display($c);
                ?>
            </div>

        </div>
        
        
        <div class="row">
            <div class="col-md-12">
                <?php
                $a = new Area('Bottom');
                $a->display($c);
                ?>
            </div>
        </div>

    </section><!-- /.content -->
</aside><!-- /.right-side -->

<?php $this->inc('elements/footer.php'); ?>