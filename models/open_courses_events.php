<?php

/*
 * Events for Open Courses Package
 * http://www.concrete5.org/documentation/developers/system/events
 * 2DO: Doc
 */

class OpenCoursesEvents extends Model {

    public function on_user_delete($userInfo) {
        // remove all user states for sessions
        Loader::model('open_courses_learner_state_object', 'open_courses');
        OpenCoursesLearnerStateObject::deleteByUserID($userInfo->getUserID());

        // 2DO: add notice that user left the course/user was deleted in userActitvy Log?
    }

    public function on_course_delete($course) {

        // delete learner group for course
        $groupID = $course->getAttribute('open_courses_course_learner_group_id');
        if ($groupID !== FALSE) {
            $group = Group::getByID($groupID);
            if ($group !== NULL) {
                $group->delete();
            }
        }

        // delete teacher group for course
        $groupID = $course->getAttribute('open_courses_course_teacher_group_id');
        if ($groupID !== FALSE) {
            $group = Group::getByID($groupID);
            if ($group !== NULL) {
                $group->delete();
            }
        }
        Loader::model('file_list');

        $fs = FileSet::getByID($course->getAttribute('open_courses_course_file_set_id'));
        if ($fs->fsID !== NULL) {
            $fl = new FileList();
            $fl->filterBySet($fs);

            // delete all files in fileset
            while (count($files = $fl->getPage()) > 0) {
                foreach ($files as $file) {
                    $file->delete();
                }
            }
            $fs->delete();
        }

        // delete learner states & actitvy
        Loader::model('open_courses_learner_state_object', 'open_courses');
        OpenCoursesLearnerStateObject::deleteByCourseID($course->cID, true);

        Loader::model('open_courses_user_activity_object', 'open_courses');
        OpenCoursesUserActivityObject::deleteByCourseID($course->cID, true);
    }

    public function on_session_delete($session) {
        $session = $pageToDelete;

        // delete learner states & actitvy
        Loader::model('open_courses_learner_state_object', 'open_courses');
        OpenCoursesLearnerStateObject::deleteBySessionID($session->cID);

        Loader::model('open_courses_user_activity_object', 'open_courses');
        OpenCoursesUserActivityObject::deleteBySessionID($session->cID, true);
    }

    public function on_course_learner_add($course, $user) {
        
        Loader::model('open_courses_user_activity_object', 'open_courses');
        $activity = OpenCoursesUserActivityObject::create($course->getCollectionID(), $user->getUserID(), 'learner_added_by_teacher');
    }

    public function on_course_learner_remove($course, $user) {
        Loader::model('open_courses_user_activity_object', 'open_courses');
        $activity = OpenCoursesUserActivityObject::create($course->getCollectionID(), $user->getUserID(), 'learner_removed_by_teacher');
    }

}
