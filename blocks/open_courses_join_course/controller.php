<?php

defined('C5_EXECUTE') or die("Access Denied.");

class OpenCoursesJoinCourseBlockController extends BlockController {

    protected $btName = 'Open Courses: Join Course';
    protected $btDescription = '';
    protected $btTable = 'btOpenCoursesJoinCourse';
    protected $btInterfaceWidth = "700";
    protected $btInterfaceHeight = "450";
    protected $btCacheBlockRecord = true;
    protected $btCacheBlockOutput = false;
    protected $btCacheBlockOutputOnPost = false;
    protected $btCacheBlockOutputForRegisteredUsers = false;
    protected $btCacheBlockOutputLifetime = CACHE_LIFETIME;

    public function getSearchableContent() {
        return $this->field_1_textbox_text;
    }

    public function on_start() {
        
    }

    public function view() {
        $u = new User();
        $loggedIn = $u->isLoggedIn();
        $this->set('loggedIn', $loggedIn);
    }

    public function action_join_course() {
        $u = new User();
        $loggedIn = $u->isLoggedIn();
        $this->set('loggedIn', $loggedIn);
        
        $c = Page::getCurrentPage();
        $bID = $this->bID;
        $error = "";
        $message = "";
        $courseCode = "";

        $bID = $this->bID;
        
        if ($this->isPost() && $u->isLoggedIn()) {
            $courseCode = $this->post('course_code');
            $nh = Loader::helper('navigation');

            Loader::model('open_courses_course', 'open_courses');
            $openCoursesCourse = OpenCoursesCourse::joinByCourseCode($courseCode, $u->getUserID());

            if ($openCoursesCourse instanceof OpenCoursesCourse) {
                $message = t("Congratulations! You joined the course: ") . "<a href=" . $nh->getLinkToCollection($openCoursesCourse->getCollectionObject()) . ">" . t('Go to course') . "</a>";
                $courseCode = "";
            } else {
                $error = $openCoursesCourse;
            }
        }

        
        $this->set('error', $error);
        $this->set('message', $message);
        $this->set('courseCode', $courseCode);
    }

}
