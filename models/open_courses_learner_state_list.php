<?php

defined('C5_EXECUTE') or die("Access Denied.");

class OpenCoursesLearnerStateList extends DatabaseItemList {

    private $queryCreated;
    //protected $autoSortColumns = array("routeSortNumber");
    protected $itemsPerPage = 50;

    protected function setBaseQuery() {
        $this->setQuery('SELECT * FROM btOpenCoursesLearnerState');
    }

    public function createQuery() {
        if (!$this->queryCreated) {
            $this->setBaseQuery();
            $this->queryCreated = 1;
        }
    }

    public function get($itemsToGet = 0, $offset = 0) {

        Loader::model("open_courses_learner_state_object", "open_courses");


        $learnerStates = array();
        $this->createQuery();
        $r = parent::get($itemsToGet, $offset);
        foreach ($r as $row) {
            $state = OpenCoursesLearnerStateObject::getByID($row['ID']);
            $learnerStates[] = $state;
        }
        return $learnerStates;
    }

    public function getTotal() {
        $this->createQuery();
        return parent::getTotal();
    }

    public function filterByCourseID($courseID) {
        $this->filter('eID', intval($courseID));
    }

    public function filterBySessionID($sessionID) {
        $this->filter('eID', intval($sessionID));
    }

    public function filterByUserID($userID) {
        $this->filter('uID', intval($userID));
    }

    public function filterByState($state) {
        // 2DO: check if state is possible/allowed
        $this->filter('state', $state);
    }

    /*
     * only works for sessions in courses, not for a sessionID
     */

    public static function getProgressForLearner($courseID, $userID) {

        // 2DO: cache this or solve it somehow? (attribute?)
        // (heavy db action if there a lot of courses?)
        // 2DO: this is a design problem: should we count published (or not-yet-published, but planned) pages for total or every childpage?
        Loader::model('page_list');
        $pl = new PageList();
        $pl->filterByParentID($courseID);
        $pl->filterByCollectionTypeHandle("open_courses_session");
        // we use every child page which is there right now for overall progress
        // this depends totally on the workflow (everything published or published week-for-week...
        //$pl->filterByAttribute('open_courses_is_published',true);
        $total = $pl->getTotal();
        $completed = 0;
        $list = new OpenCoursesLearnerStateList();
        $list->createQuery();
        $childIds = Page::getByID($courseID)->getCollectionChildrenArray(1);
        if (count($childIds) > 0) {
            $list->filter(false, 'eID IN (' . implode(',', $childIds) . ')');
            $list->filterByUserID($userID);
            $list->filter('state', 'completed');
            $completed = $list->getTotal();
        }

        return array(
            'total' => $total, // total session child pages of course
            'completed' => $completed, // completed sessions (marked by user)
        );
    }

    // 2DO: name must be "getAll<TABLENAME>"?
    public static function getAll() {
        Loader::model("open_courses_learner_state_object", "open_courses");
        $list = array();
        $list = new OpenCoursesLearnerStateList();
        $list->createQuery();
        $states = $list->get();
        return $states;
    }

}
