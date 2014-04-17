<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php
// Get link to parent page
global $c;
$pID = $c->getCollectionParentID();
$parent = Page::getByID($pID);
$nh = Loader::helper('navigation');
$linkParent = $nh->getLinkToCollection($parent);
?>


<div>
    <div style="float:left">
    <a class="btn" href="<?php echo $linkParent; ?>">&laquo; <?php echo t('Back'); ?></a>
    </div>
    <div style="float:right">
    <?php if ($userIsLearner && $completed !== TRUE) { ?>
        <a href="<?php echo $this->action('mark_completed'); ?>" class="btn btn-success"><i class="fa fa-check"></i> <?php echo t('Mark as completed'); ?></a>
    <?php } elseif ($userIsLearner) { ?>
        <div class="btn-group">
            <a href="" class="btn disabled btn-success"><i class="fa fa-check"></i> <?php echo t('Completed'); ?></a><a href="<?php echo $this->action('unmark_completed'); ?>" class="btn"><i class="fa fa-undo"></i></a>
        </div>
    <?php } ?>
    </div>
</div>
<br style="clear:both;">

<div class="row">
    <div class="col-md-12">
        <?php
        $a = new Area('1-Top');
        $a->display($c);
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div id="open-courses-session-content">
            <?php
            // Main Area for content
            // DO NOT DELETE THIS AREA! OTHERWISE IMPORT WILL NOT WORK!
            $a = new Area('Open-Courses-Content-Area');
            $a->setBlockLimit(1);
            $a->display($c);
            ?>
        </div>

        <?php
        $a = new Area('8-Left-Column');
        $a->display($c);
        ?>

    </div>

    <div class="col-md-4">
        <?php
        $a = new Area('4-Right-Column');
        $a->display($c);
        ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?php
        $a = new Area('6-Left-Column');
        $a->display($c);
        ?>
    </div>
    <div class="col-md-6">
        <?php
        $a = new Area('6-Right-Column');
        $a->display($c);
        ?>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <?php
        $a = new Area('1-Bottom');
        $a->display($c);
        ?>
    </div>
</div>

