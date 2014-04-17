<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

class OpenCoursesCoursePageTypeController extends Controller {

    protected $course; // collection
    protected $courseModel; // custom model
    protected $user;
    protected $userIsLearner;
    protected $userIsTeacher;

    public function on_start() {
        $this->course = Page::getCurrentPage(); // course collection object
        // validate user
        $this->user = new User();
        $loggedIn = $this->user->isLoggedIn();
        $this->set('loggedIn', $loggedIn);

        Loader::model('open_courses_course','open_courses');
        $this->courseModel = OpenCoursesCourse::getByID($this->course->getCollectionID());
        
        $this->userIsLearner = $this->courseModel->isUserLearner($this->user->getUserID());
        $this->set('userIsLearner', $this->userIsLearner);

        $this->userIsTeacher = $this->courseModel->isUserTeacher($this->user->getUserID());
        $this->set('userIsTeacher', $this->userIsTeacher);

        // error message for users who have c5 permissions, but things will not work here, because they aren't either teachers nor learners of this course:
        // everything else will be handled by c5
        if (!$this->userIsTeacher && !$this->userIsLearner) {
            echo t("Sorry, you have no permission to access this course."); // 2DO: send 403? 
            exit;
        }
    }

    public function view() {

        Loader::model('page_list');
        Loader::model('open_courses_learner_state_object', 'open_courses');
        Loader::model('open_courses_learner_state_list', 'open_courses');
        $nh = Loader::helper('navigation');



        // set link for course dashboard
        if ($this->userIsTeacher) {
            $singlePageCourseDashboard = Page::getByID(Config::get('OPEN_COURSES_PAGE_ID_COURSE_DASHBOARD')); // 2DO: check if found

            $courseDashboardLink = $nh->getLinkToCollection($singlePageCourseDashboard) . '?courseID=' . $this->course->getCollectionID();
            $this->set('courseDashboardLink', $courseDashboardLink);

            $courseAddSessionLink = $nh->getLinkToCollection($singlePageCourseDashboard) . 'add_session/?courseID=' . $this->course->getCollectionID();
            $this->set('courseAddSessionLink', $courseAddSessionLink);
        }

        // get available sessions for course
        // (page list will check (advanced) permissions automatically)
        $pl = new PageList();
        $pl->filterByParentID($this->course->getCollectionID());
        $pageType = CollectionType::getByHandle('open_courses_session');
        if (!$this->userIsTeacher) {
            $pl->filterByAttribute("open_courses_is_published", true);
        }
        $pl->filterByCollectionTypeID($pageType->ctID);
        $pl->sortByDisplayOrder();
        $pl->setItemsPerPage(10);
        $sessions = $pl->getPage();


        foreach ($sessions as &$session) {
            $session->link = $nh->getLinkToCollection($session);
            $session->title = $session->getCollectionName();
            $session->isPublished = $session->getAttribute('open_courses_is_published');
            if ($this->userIsLearner) {
                $state = OpenCoursesLearnerStateObject::getByEntityUserID($session->getCollectionID(), $this->user->getUserID());
                if ($state !== FALSE && $state->getState() == "completed") {
                    $session->completed = true;
                }
            }
        }

        // can learner complete the whole course or has he already?
        if ($this->userIsLearner) {
            $state = OpenCoursesLearnerStateObject::getByEntityUserID($this->course->cID, $this->user->getUserID());
            if ($state !== FALSE && $state->getState() == "completed") {
                $this->set('learnerCourseCompleted', true);
            } else {
                $progressArr = OpenCoursesLearnerStateList::getProgressForLearner($this->course->cID, $this->user->getUserID());
                if ($progressArr['total'] == $progressArr['completed']) {
                    $this->set('learnerCanCompleteCourse', true);
                }
            }
        }

        $paginator = $pl->getPagination();
        $this->set('paginator', $paginator);

        $this->set('sessions', $sessions);
        $this->set('pageList', $pl);
    }

    public function mark_completed() {
        Loader::model('open_courses_learner_state_object', 'open_courses');
        $state = OpenCoursesLearnerStateObject::addOrUpdateState($this->course->getCollectionID(), $this->user->getUserID(), 'completed');
        // 2DO: set & show system message?

        Loader::model('open_courses_user_activity_object', 'open_courses');
        $activity = OpenCoursesUserActivityObject::create($this->course->getCollectionID(), $this->user->getUserID(), 'course_completed');

        $this->redirect($this->course->getCollectionPath());
    }

    public function unmark_completed() {
        Loader::model('open_courses_learner_state_object', 'open_courses');
        $state = OpenCoursesLearnerStateObject::deleteByEntityUserID($this->course->getCollectionID(), $this->user->getUserID());
        // 2DO: set & show system message?

        Loader::model('open_courses_user_activity_object', 'open_courses');
        $activity = OpenCoursesUserActivityObject::create($this->course->getCollectionID(), $this->user->getUserID(), 'reset_course_complete_state');

        $this->redirect($this->course->getCollectionPath());
    }

    public function leave_course_confirm() {

        $this->set('leaveCourseConfirm', true);
        $this->view();
    }

    public function leave_course() {
        if ($this->userIsLearner && $this->isPost()) {
            // remove learner from group
            $g = Group::getByID($this->course->getAttribute('open_courses_course_learner_group_id'));
            $this->user->exitGroup($g); // 2DO: check success?
            // add statistic count
            $this->course->setAttribute("open_courses_course_stat_learner_left_count", intval($this->course->getAttribute("course_stat_learner_left_count")) + 1);

            // remove progress data for user:
            Loader::model('open_courses_learner_state_object', 'open_courses');
            OpenCoursesLearnerStateObject::deleteByCourseUserID($this->course->cID, $this->user->uID);

            Loader::model('open_courses_user_activity_object', 'open_courses');
            $activity = OpenCoursesUserActivityObject::create($this->course->getCollectionID(), $this->user->getUserID(), 'course_left');

            $this->redirect(Page::getByPath("/")->getCollectionPath());
        }
    }

    /*
     * event which is trigger when somewhere and somehow the page is deleted
     */
    public function on_page_move($c){
        // 2DO: check if moved to trash and execute on_page_delete...?
    }

    public function on_page_delete($c) {
        Events::fire('open_courses_on_course_delete', $c);
    }
}
