<?php
defined('C5_EXECUTE') or die("Access Denied.");
$nh = Loader::helper('navigation');
// use this->action (does not work on add, but who cares?)
// http://www.concrete5.org/marketplace/addons/ajax-lessons/

if ($loggedIn) {
    ?>

    <div class="open-courses-block-my-courses" id="open-courses-block-my-courses<?php echo $bID; ?>">
        <form action="<?php echo $resultTargetURL ?>" method="GET">

            <?php
            // leave other GET-Vars (e.g. paging) intact
            foreach ($hidden_fields as $name => $value) {
                echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
            }
            ?>
            <div class="box">
                <div class="box-header">

                    <?php if (!empty($field_1_textbox_text)): ?>
                        <h3 class="box-title"><?php echo htmlentities($field_1_textbox_text, ENT_QUOTES, APP_CHARSET); ?></h3>
                    <?php endif; ?>

                </div>
                <div class="box-body">
                    <div class="row" style="margin-top:4px;">
                        <div class="col-xs-3">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="relationType<?php echo $bID; ?>" id="optionsRadios1" value="learner" <?php echo ($relationType == "" || $relationType === 'learner') ? 'checked' : ''; ?> ><?php echo t('Learning'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="relationType<?php echo $bID; ?>" id="optionsRadios2" value="teacher" <?php echo $relationType === 'teacher' ? 'checked' : ''; ?>>
                                    <?php echo t('Teaching'); ?>
                                </label>
                            </div>
                        </div>

                        <div class="col-xs-3">
                            <input type="text" name="searchQuery<?php echo $bID; ?>" class="form-control" placeholder="Search..." value="<?php echo $searchQuery; ?>" />
                        </div>
                    </div>


                    <?php if ($coursesPageList->getTotal() > 0) { ?>

                        <table class="table">
                            <tr>
                                <th><?php echo t('Course'); ?></th>
                                <?php if ($relationType == "learner"): ?>
                                    <th><?php echo t('Overall Progress'); ?></th>
                                <?php endif; ?>

                            </tr>
                            <?php foreach ($courses as $course): ?>
                                <?php
                                //echo $progressArr['completed']." / ".$progressArr['total'];
                                ?>
                                <tr>
                                    <td><a href="<?php echo $course['link']; ?>" <?php
                                        // 2DO: move to a style.css
                                        if (!$course['is_published']) {
                                            echo "style='color:grey !important;''";
                                        }
                                        ?>><?php echo $course['title']; ?></td>
                                        <?php if ($relationType == "learner"): ?>
                                        <td>
                                            <div class="progress xs">
                                                <div class="progress-bar" style="width: <?php echo $course['progressPercentage']; ?>%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge"><?php echo $course['progressPercentage']; ?>%</span>
                                        </td>
                                    <?php endif; ?>

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
                        <p><?php echo t('No courses found.'); ?></p>
                    <?php } // eo total   ?>

                </div><!-- /.box-body -->
                <div class="box-footer">
                </div>
            </div><!-- /.box -->
        </form>

    </div>




    <?php if ($userCanCreateCourse) { ?>



        <div class="open-courses-block-my-courses" id="open-courses-block-create-course<?php echo $bID; ?>">
            <div class="box">
                <div class="box-header">
                    <div class="box-tools pull-right">
                        <button class="btn btn-default btn-sm" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-MINUS"></i></button>
                    </div>
                    <?php if (!empty($field_1_textbox_text)): ?>
                        <h3 class="box-title"><?php echo t('Create Course'); ?></h3>
                    <?php endif; ?>

                </div>
                <div class="box-body">

                    <?php if ($createError != ""): ?>
                        <div class="alert alert-danger alert-dismissable">
                            <i class="fa fa-ban"></i>
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <b><?php echo t('Error'); ?></b> <?php echo $createError;  ?>
                        </div>
                    <?php endif; ?>

                    <div class="row" style="margin-top:4px;">
                        <div class="col-xs-6">
                            <h4><?php echo t('Start blank'); ?></h4>
                            <?php $this->inc('elements/form_create_blank_course.php'); ?>
                        </div>
                        <div class="col-xs-6">
                            <h4><?php echo t('Upload course'); ?></h4>
                            <?php $this->inc('elements/form_upload_course.php'); ?>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    <?php } // eo userCanCreateCourse ?>




    <script type="text/javascript">
        // admin lte uses iCheck, so callback is slighty different
        // http://fronteed.com/iCheck/
        $(function() {
            var div = '#open-courses-block-my-courses<?php echo $bID; ?>';

            $('input', div).one('change ifChecked', function() {
                $('form:first', div).trigger('submit');
            });
        });

    </script>

<?php } else {
    ?>
    <p><?php echo t('You are not logged in. Please login to see your courses:'); ?> <a href="<?php echo $this->url('/login'); ?>">Login</a></p>
<?php }
?>


