<?php

defined('C5_EXECUTE') or die(_("Access Denied."));
Loader::library('crud_controller', 'open_courses');

class CourseDashboardController extends CrudController {

    protected $pkg_handle = 'open_courses';
    protected $user;
    protected $course;
    protected $session;

    public function on_start() {
        $this->error = Loader::helper('validation/error');

        $this->user = new User(); // the current logged in user

        $courseID = $this->get('courseID');
        $this->course = Page::getByID($courseID);


        if ($this->course->cID === NULL || $this->course->getCollectionTypeHandle() !== 'open_courses_course') {
            $this->render('error_not_found');
            exit;
        }

        $this->set('courseID', $this->course->cID);

        // validate session id if submitted (only in some case)
        // otherwise user could edit sessions which are not in this course
        if ($this->get('sessionID')) {
            $this->session = Page::getByID($this->get('sessionID'));
            if ($this->session === NULL || $this->session->getCollectionParentID() != $this->course->cID || $this->session->getCollectionTypeHandle() !== 'open_courses_session') {
                $this->render('no_permission');
                exit;
            }
        }

        // check if user is in teacher group of this course/session
        if (!$this->user->isLoggedIn() || !$this->user->inGroup(Group::getByID($this->course->getAttribute('open_courses_course_teacher_group_id')))) {
            $this->render('no_permission');
            exit;
        }

        // set back link:
        // 2DO: use get parameters like paging?
        $nh = Loader::helper('navigation');
        $this->set('backLink', $nh->getLinkToCollection(Page::getCurrentPage()) . '?courseID=' . $this->course->cID);

        $this->set('courseTitle', $this->course->getCollectionName());
    }

    public function view() {

        $nh = Loader::helper('navigation');

        if ($this->isPost()) {

            switch ($this->post('action')) {
                case 'add_session':
                    // 2DO: sanitize title
                    // 2DO: validate not-empty

                    $title = $this->post('session_title');
                    $data = array(
                        'name' => $title,
                        'cDescription' => ''
                    );
                    $pt = CollectionType::getByHandle("open_courses_session");
                    $newPage = $this->course->add($pt, $data);
                    $newPageLink = $nh->getLinkToCollection($newPage);
                    $this->set('message', t('Session created sucessfully. Go to it: ') . '<a href="' . $newPageLink . '">' . $title . '</a>');

                    $och = Loader::helper('open_courses', 'open_courses');
                    $theme = $och->get_configured_page_theme();
                    $newPage->setTheme($theme);

                    Events::fire('open_courses_on_session_add', $newPage);

                    break;

                case 'activate_session':
                    $sessionID = $this->post('sessionID'); // 2DO: Sanitize?
                    $session = Page::getByID($sessionID); // 2DO: Check if exists
                    $session->setAttribute("open_courses_is_published", true);

                    Events::fire('open_courses_on_session_publish', $session);

                    break;
                case 'deactivate_session':
                    $sessionID = $this->post('sessionID'); // 2DO: Sanitize?
                    $session = Page::getByID($sessionID); // 2DO: Check if exists
                    $session->setAttribute("open_courses_is_published", false);

                    Events::fire('open_courses_on_session_unpublish', $session);

                    break;
            }
            // 2DO: is this still needed?
            $this->set('courseID', $this->course->cID);
        }




        $this->set('courseLink', $nh->getLinkToCollection($this->course));
        $isPublished = $this->course->getAttribute('open_courses_is_published');
        $this->set('isPublished', $isPublished);

        $courseCode = $this->course->getAttribute('open_courses_course_code');
        $this->set('courseCode', $courseCode);

        $pl = new PageList();
        $pl->filterByParentID($this->course->getCollectionID());
        $pageType = CollectionType::getByHandle('open_courses_session');
        $pl->filterByCollectionTypeID($pageType->ctID);
        $pl->sortByDisplayOrder();
        $pl->setItemsPerPage(10);
        $sessions = $pl->getPage();

        $this->set('sessions', $sessions);
        $this->set('sessionPageList', $pl);

        $paginator = $pl->getPagination();
        $this->set('paginator', $paginator);


        $this->get_activity_timeline();
    }

    public function edit() {


        // 2DO: form validation
        if ($this->isPost()) {

            Loader::library('Validator', $this->pkg_handle);
            Loader::library('Validator/Exception', $this->pkg_handle);


            $validator = new Validator($this->post());
            $validator->filter('strip_tags')->required('Required field"')->maxLength(100, 'Maximum length for field title is 100 chars.')->validate('title');


            // check for errors
            if ($validator->hasErrors()) {
                $this->set('errors', $validator->getAllErrors());
                $this->set('title', $this->post('title'));
                $this->set('isPublished', (bool) $this->post('isPublished'));
                $this->set('courseCompletionApprovalNeeded', (bool) $this->post('open_courses_course_completion_approval_needed'));
            } else {
                $validData = $validator->getValidData();
                $this->course->setAttribute('open_courses_course_completion_approval_needed', (bool) $this->post('courseCompletionApprovalNeeded'));
                $this->course->setAttribute('open_courses_is_published', (bool) $this->post('isPublished'));
                $this->course->update(array(
                    'cName' => $validData['title'],
                ));
                $this->redirect('view' . "?courseID=" . $this->course->cID);
            }
        } else {
            $isPublished = $this->course->getAttribute('open_courses_is_published');
            $this->set('isPublished', (bool) $isPublished);

            $courseCompletionApprovalNeeded = $this->course->getAttribute('open_courses_course_completion_approval_needed');
            $this->set('courseCompletionApprovalNeeded', (bool) $courseCompletionApprovalNeeded);

            $title = $this->course->getCollectionName();
            $this->set('title', $title);
        }

        $this->render('edit');
    }

    private function validate_edit(array $post = array()) {
        $validator = new Validator($post);
        $validator->filter('strip_tags')->required('Required field"')->maxLength(100, 'Maximum length: 100 Chars')->validate('title');
        // check for errors
        if ($validator->hasErrors()) {
            throw new Validator_Exception(
            'Validation error: ', $validator->getAllErrors()
            );
        }
        return $validator->getValidData();
    }

    public function learners() {
        // get learner list
        // 2DO: move to model
        $groupID = $this->course->getAttribute('open_courses_course_learner_group_id');
        if (empty($groupID) || $groupID === FALSE) {
            throw new Exception(t('Group-ID for learner user group not found in database.'));
            exit;
        }

        $this->set('courseCode', $this->course->getAttribute('open_courses_course_code'));

        // 2DO: check if group exists
        // add or invite user
        // add_learner
        $message = "";
        if ($this->isPost()) {

            if ($this->post('add_type') == 'registered_user') {
                $ui = NULL;
                if ($this->post('username') !== "") {
                    $ui = UserInfo::getByUserName($this->post('username'));
                    if ($ui === NULL) {
                        $message .= t('Error: User could not be found in database.') . " <br />";
                    }
                }
                if ($ui === NULL && $this->post('email') !== "") {
                    $ui = UserInfo::getByEmail($this->post('email'));
                }

                if ($ui === NULL) {
                    $message .= t('Error: User could not be found in database.') . " <br />";
                } else {

                    // 2DO: move to on start?
                    Loader::model('open_courses_course', 'open_courses');
                    $courseModel = OpenCoursesCourse::getByID($this->course->getCollectionID());

                    if (!$courseModel->isUserTeacher($ui->getUserID())) {
                        // add user to course:
                        $u = $ui->getUserObject();
                        $u->enterGroup(Group::getByID($groupID)); // 2DO: check before if group exists
                        $u->refreshUserGroups(); // http://www.concrete5.org/community/forums/chat/user-added-to-group-must-log-out-first/
                        $message = t('User has been added successfully to group!');

                        Events::fire('open_courses_on_course_learner_add', $this->course, $u);

                        // 2DO: use sorting => recent added to group (to show the success immediately?
                    } else {
                        $message .= t('Error: User is already teacher in this course.') . " <br />";
                    }
                }
            }
        } // eo post action
        $this->set('message', $message);

        Loader::model('user_list');
        $ul = new UserList();
        $ul->filterByGroupID($groupID);
        $this->set('list', $ul);
        $this->set('results', $ul->getPage());


        $this->render('learners');
    }

    function remove_learner($userID) {
        // 2DO: implement success message
        $u = User::getByUserID($userID);
        $u->exitGroup(Group::getByID($this->course->getAttribute('open_courses_course_learner_group_id')));
        $u->refreshUserGroups(); // http://www.concrete5.org/community/forums/chat/user-added-to-group-must-log-out-first/

        Events::fire('open_courses_on_course_learner_remove', $this->course, $u);

        $this->redirect('learners' . "?courseID=" . $this->course->cID);
    }

    public function edit_session() {
        // 2DO: form validation

        Loader::model('attribute/type');
        Loader::model('attribute/categories/collection');

        $ak = CollectionAttributeKey::getByHandle('open_courses_session_completion_approval_needed');
        $sa = new SelectAttributeTypeController(AttributeType::getByHandle('select'));
        $sa->setAttributeKey($ak);
        $completionApprovalNeededValues = $sa->getOptions();
        $this->set('completionApprovalNeededValues', $completionApprovalNeededValues);

        if ($this->isPost()) {

            Loader::library('Validator', $this->pkg_handle);
            Loader::library('Validator/Exception', $this->pkg_handle);


            $validator = new Validator($this->post());
            $validator->filter('strip_tags')->required('Required field"')->maxLength(255, t('Maximum length for field title is 100 chars'))->validate('title');

            // check for errors
            if ($validator->hasErrors()) {
                $this->set('errors', $validator->getAllErrors());
                $this->set('title', $this->post('title'));
                $this->set('isPublished', (bool) $this->post('isPublished'));
                $this->set('completionApprovalNeededed', $this->post('completionApprovalNeeded'));
            } else {
                $validData = $validator->getValidData();
                $this->session->setAttribute('open_courses_is_published', (bool) $this->post('isPublished'));
                // 2DO: validate possible values for select attribute
                $this->session->setAttribute('open_courses_session_completion_approval_needed', $this->post('completionApprovalNeeded'));

                $this->session->update(array(
                    'cName' => $validData['title'],
                ));
                $this->redirect('view', "?courseID=" . $this->course->cID);
            }
        } else {

            $isPublished = $this->session->getAttribute('open_courses_is_published');
            $this->set('isPublished', (bool) $isPublished);

            $title = $this->session->getCollectionName();
            $this->set('title', $title);

            $completionApprovalNeeded = $this->session->getAttribute('open_courses_session_completion_approval_needed');
            if ($completionApprovalNeeded !== FALSE) {
                $selectedOptions = $completionApprovalNeeded->getOptions(); // this is an array of selectListOptions
                if (count($selectedOptions) > 0) {
                    // should only be one...
                    $this->set('completionApprovalNeeded', $selectedOptions[0]->getSelectAttributeOptionValue());
                }
            }
        }

        $this->render('edit_session');
    }

    function change_session_order() {
        if ($this->isPost()) {
            $cIDs = $this->post('cIDs');
            $cIDs = json_decode($cIDs);
            foreach ($cIDs as $displayOrder => $cID) {
                $v = array($displayOrder, $cID);
                $c = Page::getByID(intval($cID));
                $c->updateDisplayOrder($displayOrder, intval($cID));
            }

            $this->redirect('view', "?courseID=" . $this->course->cID);
        } else {
            $pl = new PageList();
            $pl->filterByParentID($this->course->getCollectionID());
            $pageType = CollectionType::getByHandle('open_courses_session');
            $pl->filterByCollectionTypeID($pageType->ctID);
            $pl->sortByDisplayOrder();
            $sessions = $pl->get();
            $this->set('sessions', $sessions);
            $this->render('change_session_order');
        }
    }

    function statistics() {
        
        // this may be a little buggy
        
        $nh = Loader::helper('navigation');
        Loader::model('page_list');
        Loader::model('open_courses_learner_state_object', 'open_courses');
        Loader::model('open_courses_learner_state_list', 'open_courses');

        $learnerGroup = Group::getByID($this->course->getAttribute("open_courses_course_learner_group_id"));
        $learnerTotal = $learnerGroup->getGroupMembersNum();

        // overall statistics:

        $list = new OpenCoursesLearnerStateList();
        $list->filterByCourseID($this->course->cID);
        $list->filterByState('completed');
        $learnerFinishedCourseTotal = $list->getTotal();

        $learnerLeftCourseTotal = intval($this->course->getAttribute("open_courses_course_stat_learner_left_count"));

        if ($learnerTotal > 0) {
            $learnerFinishedCourseTotalPercentage = round(($learnerFinishedCourseTotal / $learnerTotal) * 100);
        } else {
            $learnerFinishedCourseTotalPercentage = 0;
        }


        $this->set('learnerFinishedCourseTotal', $learnerFinishedCourseTotal);
        $this->set('learnerTotal', $learnerTotal);
        $this->set('learnerFinishedCourseTotalPercentage', $learnerFinishedCourseTotalPercentage);
        $this->set('learnerLeftCourseTotal', $learnerLeftCourseTotal);

        // individual session statics
        $pl = new PageList();
        // 2DO: ignore permissions or not?
        $pl->filterByCollectionTypeHandle("open_courses_session");
        $pl->filterByParentID($this->course->cID);
        $pl->setItemsPerPage(5);
        $totalSessions = $pl->getTotal();

        $sessions = $pl->getPage();
        $sessionResults = array();
        foreach ($sessions as &$session) {
            $session->title = $session->getCollectionName();
            $session->link = $nh->getLinkToCollection($session);
            // get progress of learners for this:
            $list = new OpenCoursesLearnerStateList();
            $list->filterBySessionID($session->cID);
            $list->filterByState('completed');
            $session->totalCompleted = $list->getTotal();
            // avoid division by zero
            if ($learnerTotal > 0) {
                $session->percentageCompleted = round(($session->totalCompleted / $learnerTotal) * 100);
            } else {
                $session->percentageCompleted = 0;
            }
        }

        $this->set('sessions', $sessions);
        $this->set('sessionPageList', $pl);

        $paginator = $pl->getPagination();
        $this->set('paginator', $paginator);

        // eo session statistics
        $this->render('statistics');
    }

    protected function get_activity_timeline() {

        $nh = Loader::helper('navigation');
        Loader::model('page_list');
        Loader::model('open_courses_user_activity_object', 'open_courses');
        Loader::model('open_courses_user_activity_list', 'open_courses');

        $list = new OpenCoursesUserActivityList();
        $list->sortBy('dateTime', "desc");
        $list->filterByCourseID($this->course->cID, true); // include childpages
        $list->setItemsPerPage(10);
        $list->setNameSpace("activityTimeline");

        $activities = $list->getPage();

        $this->set('activities', $activities);
        $this->set('activityList', $list);

        $paginator = $list->getPagination();
        $this->set('activityPaginator', $paginator);

        //$this->render('activity_timeline');
    }

    public function delete_session_check() {
        if ($this->isPost()) {
            // event hook will take care of delete process
            $this->session->delete();
            $this->redirect('view', "?courseID=" . $this->course->cID); // 2do append message? / or redirect to delete_session_successful (no POST action..)
        } else {
            $this->render('delete_session_check');
        }
    }

}
