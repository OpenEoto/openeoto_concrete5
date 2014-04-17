<?php
// 2DO: only accesible if teacher
defined('C5_EXECUTE') or die(_("Access Denied."));
$nh = Loader::helper('navigation');
$vt = Loader::helper('validation/token');
$dh = Loader::helper('date');
?>



<div class="margin">
    <a href="<?php echo $courseLink; ?>">&laquo; <?php echo t('Back to Course WebView'); ?></a>
</div>

<h2 class="page-header"><?php echo $courseTitle; ?></h2>



<div class="row">
    <div class="col-md-12">

        <?php if ($message != ""): ?>
            <div class="alert alert-info alert-dismissable">
                <i class="fa fa-info"></i>
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>


        <?php if (!$isPublished): ?>

            <div class="alert alert-warning alert-dismissable">
                <i class="fa fa-warning"></i>
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <?php echo t('This course is in draft-mode:'); ?><a href="<?php echo $this->action('edit') . "?courseID=" . $courseID; ?> "> <?php echo t('Activate it here'); ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>


<div class="row">    
    <div class="col-md-6">

        <div>

            <a class="btn btn-app" href="<?php echo $this->action('edit') . "?courseID=" . $courseID; ?>">
                <i class="fa fa-edit"></i> <?php echo t('Edit Settings'); ?>
            </a>

            <a class="btn btn-app" href="<?php echo $this->action('statistics') . '?courseID=' . $courseID; ?>">
                <i class="fa fa-bar-chart-o"></i> <?php echo t('Statistics'); ?>
            </a>

            <a class="btn btn-app" href="<?php echo $this->action('learners') . "?courseID=" . $courseID; ?>">
                <i class="fa fa-users"></i> <?php echo t('Learners'); ?>
            </a>



            <?php if ($isPublished): ?>
                <div class="form-group">
                    <label>
                        <?php echo t('You can invite learner via this course invite code:'); ?> </label><input class="form-control" value="<?php echo $courseCode; ?>" />
                </div>
            <?php endif; ?>

        </div>

        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><?php echo t('Manage sessions'); ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-6">
                        <form method="POST">
                            <div class="input-group input-group-sm">
                                <input type="hidden" name="action" value="add_session">
                                <input type="text" name="session_title" class="form-control" placeholder="<?php echo t('Session title...'); ?>">
                                <span class="input-group-btn">
                                    <button class="btn btn-flat" type="submit"><?php echo t('Add Session'); ?></button>
                                </span>
                            </div>
                            <?php print $vt->output(); ?>
                        </form>                      
                    </div>

                    <div class="col-xs-6">
                        <a class="btn btn-sm" href="<?php echo $this->action('change_session_order') . '?courseID=' . $courseID; ?>"><?php echo t('Change order'); ?></a>               
                    </div>
                </div>

                <?php if ($sessionPageList->getTotal() > 0) { ?>

                    <table class="table">
                        <tr>
                            <th><?php echo t('Active?'); ?></th>
                            <th><?php echo t('Title'); ?></th>
                            <th style="min-width:175px;"><?php echo t('Action'); ?></th>
                        </tr>
                        <?php foreach ($sessions as $session): ?>
                            <?php
                            $displayOrder = $session->getCollectionDisplayOrder();
                            ?>
                            <tr>
                                <td>
                                    <?php if ((bool) $session->getAttribute('open_courses_is_published') === TRUE): ?>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="deactivate_session" />
                                            <input type="hidden" name="sessionID" value="<?php echo $session->cID; ?>" />
                                            <button class="btn btn-sm btn-primary" type="submit"><i class="fa fa-check-circle"></i></button>
                                            <?php print $vt->output(); ?>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="activate_session" />
                                            <input type="hidden" name="sessionID" value="<?php echo $session->cID; ?>" />
                                            <button class="btn btn-sm" type="submit"><i class="fa fa-lock"></i></button>
                                            <?php print $vt->output(); ?>
                                        </form>
                                    <?php endif; ?>



                                </td>
                                <td><a href="<?php echo $nh->getLinkToCollection($session); ?>" <?php
                                    // 2DO: css style
                                    if (!$session->getAttribute('open_courses_is_published')) {
                                        echo "style='color:grey !important;''";
                                    }
                                    ?>><?php echo $session->getCollectionName(); ?></td>
                                <td>
                                    <a class="btn btn-primary btn-sm" href="<?php echo $this->action('edit_session') . "?sessionID=" . $session->cID . "&courseID=" . $courseID; ?>"><?php echo t('Edit'); ?></a>
                                    <a class="btn btn-danger btn-sm" href="<?php echo $this->action('delete_session_check') . "?sessionID=" . $session->cID . "&courseID=" . $courseID; ?>"><?php echo t('Delete'); ?></a>
                                </td>
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
                <?php } // eo total  ?>

            </div><!-- /.box-body -->
        </div>

    </div><!-- // eo col-md-6 -->



    <div class="col-md-6">




        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><?php echo t('Timeline of activities'); ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php if ($activityList->getTotal() > 0) { ?>
                            <ul class="timeline">
                                <?php
                                foreach ($activities as $activity) {
                                    $userName = User::getByUserID($activity->getUserID())->getUserName(); // 2DO: check deleted users
                                    ?>

                                    <li>
                                        <i class="fa <?php echo $activity->getIconClass(); ?>"></i>
                                        <div class="timeline-item">
                                            <?php // 2DO: USE USER DATE MASK FORMAT OR SYSTEMWIDE FORMAT? (d.m.Y H:i:s)?   ?>
                                            <span class="time"><i class="fa fa-clock-o"></i> <?php echo $dh->getLocalDateTime($activity->getDateTime()); ?></span>
                                            <h3 class="timeline-header no-border"><?php echo $activity->getDescription(); ?> <a href="<?php echo $activity->getEntityLink(); ?>">&raquo;</a></h3>

                                            <div class="timeline-footer">

                                            </div>
                                        </div>
                                    </li>

                                    <?php //print_r($activity);  ?>

                                <?php } // eo feach activities   ?>


                                <div class="box-tools">
                                    <ul class="pagination pagination-sm no-margin pull-right">
                                        <li><?php echo $activityPaginator->getPrevious('&laquo;') ?></li>
                                        <?php echo $activityPaginator->getPages('li'); ?>
                                        <li><?php echo $activityPaginator->getNext('&raquo;'); ?></li>
                                    </ul>
                                </div>
                            </ul>
                        <?php } // eo getTotal > 0   ?>
                    </div>
                </div>

            </div><!-- /.box-body -->
        </div>

    </div>  <!-- // eo col-md-6 -->
</div> <!-- // eo row -->