<?php
defined('C5_EXECUTE') or die("Access Denied.");
global $c;
?>

<?php if ($leaveCourseConfirm): ?>
    <div class="row">
        <form method="POST" action="<?php echo $this->action('leave_course'); ?>">
            <div class="alert alert-danger alert-dismissable">
                <i class="fa fa-ban"></i>
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <b><?php echo t('Alert!'); ?></b> <?php echo t('Do you want to leave the course? All progress will be lost and you can\'t undo this! Are you sure?'); ?>
                <br />
                <br />

                <button type="submit"><?php echo t('Yes, delete my data.'); ?></button>
                <button data-dismiss="alert"><?php echo t('Cancel'); ?></button>

            </div>
        </form>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <?php
        $a = new Area('1-Top');
        $a->display($c);
        ?>
    </div>
</div>
<div class="row">

    <div class="col-md-6">
        <?php
        $a = new Area('2-Left-Column-Before');
        $a->display($c);
        ?>

        <div class="box box-primary" id="open-courses-session-overview-box">
            <div class="box-header">
                <!-- tools box -->
                <div class="pull-right box-tools">
                    <?php if ($userIsTeacher) { ?>
                        <a href="<?php echo $courseDashboardLink; ?>"><button class="btn btn-primary"><i class='fa fa-desktop'></i> <?php echo t('Teacher dashboard'); ?>
                            </button></a>
                    <?php }//eo isTeacher
                    ?>
                    <?php if ($userIsLearner && !$learnerCourseCompleted) { ?>
                        <div class="btn-group">
                            <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-gear"></i>
                            </button>
                            <ul class="dropdown-menu">

                                <li><a href="<?php echo $this->action('leave_course_confirm'); ?>" class="btn"><i class="fa fa-sign-out"></i> <?php echo t('Leave course'); ?></a> </li>       

                            </ul>
                        </div>

                    <?php } ?>


                    <?php if ($userIsLearner && $learnerCanCompleteCourse) { ?>
                        <a href="<?php echo $this->action('mark_completed'); ?>" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> <?php echo t('Finish course'); ?></a>
                    <?php } elseif ($userIsLearner && $learnerCourseCompleted) { ?>
                        <div class="btn-group">
                            <a href="" class="btn disabled btn-sm"><i class="fa fa-check"></i> <?php echo t('Completed'); ?></a><a href="<?php echo $this->action('unmark_completed'); ?>" class="btn"><i class="fa fa-undo"></i></a>
                        </div>
                    <?php } ?>

                </div><!-- /. tools -->

                <h3 class="box-title"><i class="glyphicon glyphicon-expand"></i> <?php echo t('Sessions'); ?></h3>
            </div>

            <div class="box-body no-padding">
                <?php if ($pageList->getTotal() > 0) { ?>

                    <table class="table">
                        <tr>
                            <th><?php echo t('Session'); ?></th>
                            <th>Status</th>
                        </tr>
                        <?php foreach ($sessions as $session): ?>
                            <tr>
                                <td><a href="<?php echo $session->link; ?>">
                                        <?php echo $session->title; ?></a></td>

                                <td><?php if ($session->completed): ?>
                                        <span class="label label-success"><?php echo t('Completed'); ?></span>
                                    <?php endif; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <div class="box-tools">
                        <ul class="pagination pagination-sm no-margin pull-right">
                            <li><?php echo $paginator->getPrevious('&laquo;') ?></li>
                            <?php echo $paginator->getPages('li'); ?>
                            <li><?php echo $paginator->getNext('&raquo;'); ?></li>
                        </ul>
                    </div>
                <?php } else { ?>
                    <p><?php echo t('No sessions found.'); ?></p>
                <?php } // eo total   ?>
            </div>

        </div> <!-- // eo box overview courses -->

        <?php
        $a = new Area('2-Left-Column-After');
        $a->display($c);
        ?>


    </div><!-- // eo left column -->
    <!-- right column -->
    <div class="col-md-6">
        <?php
        $a = new Area('2-Right-Column');
        $a->display($c);
        ?>
    </div>
</div>





<div id="open-courses-overview-area">
    <?php
// Main Area for content
    $a = new Area('Open-Courses-Overview-Area');
    $a->display($c);
    ?>
</div>


<div class="box-body no-padding">



    <div>
        <?php
// Main Area for content
        $a = new Area('Open-Courses-Course-Bottom-Area');
        $a->display($c);
        ?>
    </div>