<?php defined('C5_EXECUTE') or die(_("Access Denied.")); ?>

<a href="<?php echo $this->action('view').'?courseID='.$courseID; ?>">&laquo; <?php echo t('Course dashboard'); ?></a>

<h2 class="page-header"><?php echo $courseTitle; ?></h2>
    


<div class="row">
    <div class="col-md-6">


        <div class="row">
            <div class="col-lg-6 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>
                            <?php echo $learnerFinishedCourseTotalPercentage; ?> <sup style="font-size: 20px">%</sup>
                        </h3>
                        <p>
                            <?php echo t('of all learners finished the course'); ?>
                        </p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        More info <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div><!-- ./col -->
            <div class="col-lg-6 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>
                            <?php echo $learnerTotal; ?>
                        </h3>
                        <p>
                            <?php echo t('Total active learners'); ?>
                        </p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        More info <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div><!-- ./col -->
        </div>
        
        <h2>Session progress</h2>


        <?php if ($sessionPageList->getTotal() > 0) { ?>

            <table class="table">
                <tr>
                    <th><?php echo t('Active?'); ?></th>
                    <th><?php echo t('Title'); ?></th>
                    <th style="min-width:75px;">%</th>
                    <th><?php echo t('Progress'); ?></th>
                    
                </tr>
                <?php foreach ($sessions as $session): ?>
                    <tr>
                        <td>
                            <?php if ((bool) $session->getAttribute('open_courses_is_published') === TRUE): ?>
                                <i class="fa fa-unlock"></i>
                            <?php else: ?>
                                <i class="fa fa-lock"></i>
                            <?php endif; ?>
                        </td>
                        <td><a href="<?php echo $this->session->link; ?>" <?php
                            // 2DO: css style
                            if (!$session->getAttribute('open_courses_is_published')) {
                                echo "style='color:grey !important;''";
                            }
                            ?>><?php echo $session->title; ?></td>
                        <td>
                            <span class="badge bg-light-blue"><?php echo $session->percentageCompleted; ?>%</span>
                            
                        </td>
                        <td> 
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo $session->percentageCompleted; ?>%"></div>
                            </div>
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
            <?php } // eo total  
        ?>
            

    </div><!-- //eo col-md6 -->
    
    <div class="col-md-6">
          <div class="row">
            <div class="col-lg-6 col-xs-6">
            </div><!-- ./col -->
            <div class="col-lg-6 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>
                            <?php echo $learnerLeftCourseTotal; ?>
                        </h3>
                        <p>
                            <?php echo t('Learner(s) left this course.'); ?>
                        </p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        More info <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div><!-- ./col -->
        </div>
    </div>
    
    <h2>Individual learner progress</h2>
    
    <input> Search
</div>