<?php

defined('C5_EXECUTE') or die("Access Denied.");

class OpenCoursesUserActivityObject extends Object {

    public static function getByID($id) {
        $db = Loader::db();
        $data = $db->getRow('SELECT * FROM btOpenCoursesUserActivity WHERE ID = ?', array($id));
        if (!empty($data)) {
            $userActivity = new OpenCoursesUserActivityObject();
            $userActivity->setPropertiesFromArray($data);
        }
        return (is_a($userActivity, "OpenCoursesUserActivityObject")) ? $userActivity : false;
    }

    public static function create($entityID, $userID, $event, $userRole = 'learner', $data = array()) {
        $dh = Loader::helper('date');
        $dateTime = $dh->getSystemDateTime("now", 'Y-m-d H:i:s');
        $vals = array($entityID, $userID, $userRole, $event, json_encode($data), $dateTime);

        $db = Loader::db();
        $db->query("INSERT INTO btOpenCoursesUserActivity (eID,uID,userRole,event,jsonData,dateTime) VALUES (?,?,?,?,?,?)", $vals);
        $id = $db->_insertID();
        if (intval($id) > 0) {
            return OpenCoursesUserActivityObject::getByID($id);
        } else {
            return false; // 2DO: log/show error?
        }
    }

    public static function deleteByCourseID($courseID, $includeChildPages = false) {
        $course = Page::getByID($courseID);
        $childPagesIds = array();
        if ($includeChildPages) {
            $childPageIds = $course->getCollectionChildrenArray(1);
            $childPageIds[] = $courseID;
        } else {
            $childPageIds[] = $courseID;
        }
        $idsString = implode(",", $childPageIds);
        $db = Loader::db();
        $db->query("DELETE FROM btOpenCoursesUserActivity WHERE eID IN ({$idsString})");
    }
    
    public static function deleteBySessionID($ID) {
        $db = Loader::db();
        $db->query("DELETE FROM btOpenCoursesUserActivity WHERE eID ={$ID}");
    }

    public function getID() {
        return intval($this->ID);
    }

    // 2DO: rename to collection? (entity was meant session/course)
    public function getEntityID() {
        return intval($this->eID);
    }

    public function getUserID() {
        return $this->uID;
    }

    public function getEvent() {
        return $this->event;
    }

    public function getData() {
        return json_decode($this->jsonData);
    }

    public function getDateTime() {
        return $this->dateTime;
    }

    public function getDescription() {
        $userName = User::getByUserID($this->getUserID())->getUserName(); // 2DO: deleted users?
        $entityTitle = Page::getByID($this->getEntityID())->getCollectionName();
        // 2DO: how can devs extend this?
        switch ($this->getEvent()) {
            case 'course_completed':
                return t("%s completed session '%s'", $userName, $entityTitle);
                break;
            case 'session_completed':
                return t("%s completed session '%s'", $userName, $entityTitle);
                break;
            case 'session_reset_complete_state':
                return t("%s unmarked session '%s' as completed", $userName, $entityTitle);
                break;
            case 'course_reset_complete_state':
                return t("%s unmarked course '%s' as completed", $userName, $entityTitle);
                break;
            case 'course_left':
                return t("%s left course '%s'", $userName, $entityTitle);
            case 'course_joined':
                return t("%s joined course '%s'", $userName, $entityTitle);
            case 'learner_added_by_teacher':
                return t("%s added to course '%s' by teacher", $userName, $entityTitle);
            case 'learner_removed_by_teacher':
                return t("%s removed from course '%s' by teacher", $userName, $entityTitle);
            default:
            // 2DO: not found default message
        }
    }

    public function getIconClass() {
        switch ($this->getEvent()) {
            case 'course_completed':
                return 'fa-check bg-green';
            case 'session_completed':
                return 'fa-check bg-yellow';
            case 'session_reset_complete_state':
                return 'fa-undo';
            case 'course_reset_complete_state':
                return 'fa-undo';
            case 'course_left':
                return 'fa-sign-out bg-red';
            case 'learner_removed_by_teacher':
                return 'fa-ban bg-red';
            case 'course_joined':
                return 'fa-user bg-green';
            case 'learner_added_by_teacher':
                return 'fa-sign-in';
            default:
                return 'fa-user';
        }
    }

    public function getEntityLink() {
        $nh = Loader::helper('navigation');
        // 2DO: check if page exists (maybe deleted afterwards)
        $page = Page::getByID($this->getEntityID());
        if ($page->cID !== NULL) {
            return $nh->getLinkToCollection($page);
        }
        return '#';
    }

}
