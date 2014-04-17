<?php
defined('C5_EXECUTE') or die("Access Denied.");
$nh = Loader::helper('navigation');
// use this->action (does not work on add, but who cares?)
// http://www.concrete5.org/marketplace/addons/ajax-lessons/
if ($loggedIn) {
    ?>

    <div class="open-courses-block-my-courses" id="open-courses-block-my-courses<?php echo $bID; ?>">
        <form method="POST" action="<?php echo $this->action('join_course'); ?>">
            <input type="hidden" name="action" value="open_courses_join_course" />
            <div class="box">
                <div class="box-header">
                    <?php if (!empty($field_1_textbox_text)): ?>
                        <h3 class="box-title"><?php echo htmlentities($field_1_textbox_text, ENT_QUOTES, APP_CHARSET); ?></h3>
                    <?php endif; ?>
                    
                </div>
                <div class="box-body">

                    <?php if ($error != ""): ?>
                        <div class="alert alert-danger alert-dismissable">
                            <i class="fa fa-ban"></i>
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <b><?php echo t('Error'); ?></b> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($message != ""): ?>
                        <div class="alert alert-success alert-dismissable">
                            <i class="fa fa-check"></i>
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <div class="input-group input-group-sm">
                        <input type='text' name="course_code" class="form-control" placeholder="<?php echo t('Invite code'); ?>" value="<?php echo $courseCode; ?>"/>
                        <span class="input-group-btn">
                            <button class="btn btn-info btn-flat" type="submit"><?php echo t('Join'); ?></button>
                        </span>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <?php
}// eo loggedin