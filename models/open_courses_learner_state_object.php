<?php

defined('C5_EXECUTE') or die("Access Denied.");

// i'm sorry, this one is really quick&dirty
// anyway, thanks to:
// http://www.concrete5tutorials.com/block-tutorials/interfacing-with-the-database/
// http://www.werstnet.com/blog/using-custom-objects-and-lists-to-create-concrete5-dashboard-pag/
class OpenCoursesLearnerStateObject extends Object {

    protected $states = array('completed', 'cancelled', 'started'); // 2DO: check inserts

    static function getByID($id) {
        $db = Loader::db();
        $data = $db->getRow('SELECT * FROM btOpenCoursesLearnerState WHERE ID = ?', array($id));
        if (!empty($data)) {
            $learnerStateObject = new OpenCoursesLearnerStateObject();
            $learnerStateObject->setPropertiesFromArray($data);
        }
        return (is_a($learnerStateObject, "OpenCoursesLearnerStateObject")) ? $learnerStateObject : false;
    }

    // 2DO: entity is not really necessary because both are pages, so they can't have same ID

    public static function getByEntityUserID($entityID, $userID) {
        $db = Loader::db();
        $data = $db->getRow('SELECT * FROM btOpenCoursesLearnerState WHERE uID = ? AND eID = ?', array($userID, $entityID));
        if (!empty($data)) {
            $learnerStateObject = new OpenCoursesLearnerStateObject();
            $learnerStateObject->setPropertiesFromArray($data);
        }
        return (is_a($learnerStateObject, "OpenCoursesLearnerStateObject")) ? $learnerStateObject : false;
    }

    public static function addOrUpdateState($entityID, $userID, $state) {
        $db = Loader::db();
        // 2DO: check if it is already there?
        $vals = array($entityID, $userID, $state, $state);
        // 2DO: only working for mysql > 5.1
        // hack suggested by stackoverflow: http://stackoverflow.com/questions/2634152/getting-mysql-insert-id-while-using-on-duplicate-key-update-with-php
        $db->query("INSERT INTO btOpenCoursesLearnerState (eID,uID,state) VALUES (?,?,?) ON DUPLICATE KEY UPDATE state=?,id=LAST_INSERT_ID(id)", $vals);
        $id = $db->_insertID();
        if (intval($id) > 0) {
            return OpenCoursesLearnerStateObject::getByID($id); // 2DO: do we have a object here already?
        } else {
            return false; // 2DO: log/show error?
        }
    }

    public static function deleteByEntityUserID($entityID, $userID) {
        $db = Loader::db();
        $db->execute("DELETE FROM btOpenCoursesLearnerState where eID = ? AND uID = ?", array($entityID, $userID));
    }

    public static function deleteByUserID($userID) {
        if ($userID !== NULL && (intval($userID) > 0)) {
            $db = Loader::db();
            $db->execute("DELETE FROM btOpenCoursesLearnerState WHERE uID = ?", array(intval($userID)));
        }
    }

    public function delete() {
        $db = Loader::db();
        $ID = $this->getID(); // 2DO: is it possible that this is empty?
        if ($this->ID !== NULL) {
            $db->execute("DELETE FROM btOpenCoursesLearnerState where ID = ?", array($ID));
        }
    }

    /*
     * deletes user state for course and (child) sessions 
     */

    public static function deleteByCourseUserID($courseID, $userID) {
        // 2DO: check if NULL
        $course = Page::getByID($courseID);
        $childPageIds = $course->getCollectionChildrenArray(1);
        $childPageIds[] = $courseID;
        $idsString = implode(",", $childPageIds);
        $db = Loader::db();
        // 2DO. validate values...
        $db->query("DELETE FROM btOpenCoursesLearnerState where uID = {$userID} AND eID IN ({$idsString})");
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
        $db->query("DELETE FROM btOpenCoursesLearnerState WHERE eID IN ({$idsString})");
    }

    // 2DO: check this method
    public static function deleteBySessionID($ID) {
        $db = Loader::db();
        $db->query("DELETE FROM btOpenCoursesLearnerState WHERE eID = {$ID}");
    }

    public function getID() {
        return intval($this->ID);
    }

    public function getEntityID() {
        return intval($this->eID);
    }

    public function getEntity() {
        return $this->entity;
    }

    public function getState() {
        return $this->state;
    }

}
