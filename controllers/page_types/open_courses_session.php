<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

class OpenCoursesSessionPageTypeController extends Controller {

    protected $session;
    protected $course;
    protected $courseModel; // custom model
    protected $user;
    protected $userIsLearner;
    protected $userIsTeacher;

    public function on_start() {
        $this->session = Page::getCurrentPage(); // course collection object
        $this->course = Page::getByID($this->session->getCollectionParentID());


        // validate user
        $this->user = new User();
        $loggedIn = $this->user->isLoggedIn();
        $this->set('loggedIn', $loggedIn);

        Loader::model('open_courses_course', 'open_courses');
        $this->courseModel = OpenCoursesCourse::getByID($this->course->getCollectionID());

        $this->userIsLearner = $this->courseModel->isUserLearner($this->user->getUserID());
        $this->set('userIsLearner', $this->userIsLearner);

        $this->userIsTeacher = $this->courseModel->isUserTeacher($this->user->getUserID());
        $this->set('userIsTeacher', $this->userIsTeacher);

        // error message for users who have c5 permissions, but things will not work here, because they aren't either teachers nor learners of this course:
        // everything else will be handled by c5 (page forbidden)
        if (!$this->userIsTeacher && !$this->userIsLearner) {
            echo t("Sorry, you have no permission to access this session."); // 2DO: send 403? how?
            exit;
        }
    }

    public function view() {

        if ($this->userIsLearner) {
            Loader::model('open_courses_learner_state_object', 'open_courses');
            // 2DO: check if session is already marked as completed
            $state = OpenCoursesLearnerStateObject::getByEntityUserID($this->session->getCollectionID(), $this->user->getUserID());
            if ($state !== FALSE && $state->getState() == "completed") {
                $this->set('completed', true);
            }
        }
    }

    public function mark_completed() {

        // 2DO: use c5 events system for event handling?

        Loader::model('open_courses_learner_state_object', 'open_courses');
        $state = OpenCoursesLearnerStateObject::addOrUpdateState($this->session->getCollectionID(), $this->user->getUserID(), 'completed');
        Loader::model('open_courses_user_activity_object', 'open_courses');
        $activity = OpenCoursesUserActivityObject::create($this->session->getCollectionID(), $this->user->getUserID(), 'session_completed');

        $this->redirect($this->session->getCollectionPath());
    }

    public function unmark_completed() {
        Loader::model('open_courses_learner_state_object', 'open_courses');
        $state = OpenCoursesLearnerStateObject::deleteByEntityUserID($this->session->getCollectionID(), $this->user->getUserID());

        Loader::model('open_courses_user_activity_object', 'open_courses');
        $activity = OpenCoursesUserActivityObject::create($this->session->getCollectionID(), $this->user->getUserID(), 'session_reset_complete_state');
        $this->redirect($this->session->getCollectionPath());
    }

    public function on_page_delete($c) {

        Events::fire('open_courses_on_course_delete', $c);
    }

}
