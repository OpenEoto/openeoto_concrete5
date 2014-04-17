<?php

class OpenCoursesCourse {

    private $cObj; // the collection Object (Page) - with this you can perform all concrete5 API actions

    public static function getByID($cID) {
        $cObj = Page::getByID($cID);
        if ($cObj->cID === NULL) {
            throw new Exception(t("Course collection page not found ID:" . $cID));
        }
        // 2DO: check if page is collection of page type: open_courses_course
        $c = new OpenCoursesCourse();
        $c->setCollectionObject($cObj);
        return $c;
    }

    public static function joinByCourseCode($courseCode, $userID) {
        // 2DO: check if course_code not empty 

        $error = "";

        $u = User::getByUserID($userID); // 2DO: check

        Loader::model('page_list');
        $pl = new PageList();
        $pl->filterByAttribute("open_courses_course_code", $courseCode);
        $pl->filterByAttribute("open_courses_is_published", true);
        $pl->filterByCollectionTypeHandle("open_courses_course");
        // important: otherwise course will not be found
        $pl->ignorePermissions();
        if ($pl->getTotal() == 0) {
            $error = t("Server-Error: Course not found or course is not published yet. Please notify the administrator or teacher if this happens again.");
            return $error;
        }

        $pages = $pl->get(1);
        $course = $pages[0];

        $openCoursesCourse = OpenCoursesCourse::getByID($course->getCollectionID());

        if ($openCoursesCourse->isUserTeacher($userID)) {
            $error = t('User is already teacher. You can\'t have both roles in a course.');
            return $error;
        }

        $groupID = $course->getAttribute('open_courses_course_learner_group_id');
        if (empty($groupID)) {
            Log::addEntry("Course Group Learners not found for ID:" . $course->cID . " ", "error");
            $error = t("Server-Error. Please notify the administrator if this happens again.");
            return $error;
        }

        $groupCourseLearners = Group::getByID($groupID); //2DO: check if exists
        // 2DO: check if user is already in group and send warning message
        // 2DO: use a model?
        $u->enterGroup($groupCourseLearners);
        $u->refreshUserGroups(); // http://www.concrete5.org/community/forums/chat/user-added-to-group-must-log-out-first/

        Loader::model('open_courses_user_activity_object', 'open_courses');
        $activity = OpenCoursesUserActivityObject::create($course->getCollectionID(), $u->getUserID(), 'course_joined');



        Events::fire('open_courses_on_learner_course_join', $course, $u);



        return $openCoursesCourse;
    }

    public function addTeacherToCourse($userID) {

        $u = User::getByUserID($userID);

        // add user to general teacher group (important for dashboard access permission)
        $teacherGroup = Group::getByID(Config::get('OPEN_COURSES_TEACHER_GROUP_ID'));
        $u->enterGroup($teacherGroup);
        $u->refreshUserGroups(); // http://www.concrete5.org/community/forums/chat/user-added-to-group-must-log-out-first/

        $groupID = $this->cObj->getAttribute('open_courses_course_teacher_group_id');
        if (empty($groupID)) {
            throw new Exception(t('Group-ID for teacher user group not found in database.'));
        }

        $groupCourseTeachers = Group::getByID($groupID); //2DO: check if exists
        $u->enterGroup($groupCourseTeachers);
        $u->refreshUserGroups(); // http://www.concrete5.org/community/forums/chat/user-added-to-group-must-log-out-first/

        Events::fire('open_courses_on_course_teacher_add', $this->cObj, $u);

        return true;
    }

    public function removeTeacherFromCourse($userID) {

        $user = User::getByUserID($userID);
        $group = Group::getByID($this->cObj->getAttribute('open_courses_course_teacher_group_id'));
        $user->exitGroup($group);
        $user->refreshUserGroups(); // http://www.concrete5.org/community/forums/chat/user-added-to-group-must-log-out-first/

        Events::fire('open_courses_on_course_teacher_remove', $this->cObj, $u);

        return true;
    }

    public function isUserLearner($userID) {
        $u = User::getByUserID($userID);
        return $u->inGroup(Group::getByID($this->cObj->getAttribute('open_courses_course_learner_group_id')));
    }

    public function isUserTeacher($userID) {
        $u = User::getByUserID($userID);
        return $u->inGroup(Group::getByID($this->cObj->getAttribute('open_courses_course_teacher_group_id')));
    }

    public function getCollectionObject() {
        return $this->cObj;
    }

    protected function setCollectionObject($cObj) {
        $this->cObj = $cObj;
    }

}
