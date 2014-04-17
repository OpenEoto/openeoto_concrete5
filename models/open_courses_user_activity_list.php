<?php

defined('C5_EXECUTE') or die("Access Denied.");

class OpenCoursesUserActivityList extends DatabaseItemList {

    private $queryCreated;
    protected $autoSortColumns = array("dateTime","ASC");
    protected $itemsPerPage = 50;

    protected function setBaseQuery() {
        $this->setQuery('SELECT * FROM btOpenCoursesUserActivity');
    }

    public function createQuery() {
        if (!$this->queryCreated) {
            $this->setBaseQuery();
            $this->queryCreated = 1;
        }
    }

    public function get($itemsToGet = 0, $offset = 0) {

        Loader::model("open_courses_user_activity_object", "open_courses");

        $userActivities = array();
        $this->createQuery();
        $r = parent::get($itemsToGet, $offset);
        foreach ($r as $row) {
            $state = OpenCoursesUserActivityObject::getByID($row['ID']);
            $userActivities[] = $state;
        }
        return $userActivities;
    }

    public function getTotal() {
        $this->createQuery();
        return parent::getTotal();
    }

    public function filterByCourseID($courseID, $includeChildSessions = false) {
        if ($includeChildSessions) {
            $childIds = Page::getByID($courseID)->getCollectionChildrenArray(1);
            $childIds[] = $courseID;
            $this->filter(false, 'eID IN (' . implode(',', $childIds) . ')');
        }
        else{
             $this->filter('eID', intval($courseID));
        }
    }

    public function filterBySessionID($sessionID) {
        $this->filter('eID', intval($sessionID));
    }

    public function filterByUserID($userID) {
        $this->filter('uID', intval($userID));
    }

}
