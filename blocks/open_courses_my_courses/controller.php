<?php

defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Block shows Courses which are joined by the user
 * (Based on advanced permission for course-page)
 */
class OpenCoursesMyCoursesBlockController extends BlockController {

    protected $btName = 'Open Courses: My Courses (Search)'; // 2DO: translate in function?
    protected $btDescription = '';
    protected $btTable = 'btOpenCoursesMyCourses';
    protected $btInterfaceWidth = "700";
    protected $btInterfaceHeight = "450";
    protected $btCacheBlockRecord = false;
    protected $btCacheBlockOutput = false;
    protected $btCacheBlockOutputOnPost = false;
    protected $btCacheBlockOutputForRegisteredUsers = false;
    protected $btCacheBlockOutputLifetime = CACHE_LIFETIME;
    protected $redirectToAnotherPage = false; // Not implemented yet (see concrete5 search block for explanation)

    public function getSearchableContent() {
        return $this->field_1_textbox_text;
    }
    
    // 2DO: Implement ajax filter
    public function view() {
        $bID = $this->bID;
        $c = Page::getCurrentPage();
        $u = new User();
        $loggedIn = $u->isLoggedIn();
        $this->set('loggedIn', $loggedIn);
        $this->set('userID', $u->uID);
        if ($loggedIn) {

            // submit url for form

            $uh = Loader::helper("url");

            //$resultsURL = $c->getCollectionPath();
            // Search can be redirected to another page to display results in full page, but right now we do not allow it in edit.php
            // see concrete5 search block for more information
            if (!$this->redirectToAnotherPage) {
                $resultsURL = $_SERVER["REQUEST_URI"];
            }

            $this->set('resultTargetURL', $resultsURL);

            Loader::model('page_list');
            $pl = new PageList();
            // important: set namespace for page list, so pagination does not interfers with other page list blocks
            $pl->setNameSpace('b' . $this->bID);
            //$pageType = CollectionType::getByHandle('open_courses_course');
            $pl->filterByCollectionTypeHandle("open_courses_course");
            $pl->setItemsPerPage(10);

            switch ($this->get('relationType' . $bID)) {
                case 'teacher':
                    $relationType = "teacher";
                    $pl->filter(false, "ak_open_courses_course_teacher_group_id IN (SELECT UserGroups.gID FROM UserGroups WHERE UserGroups.uID = " . $u->getUserID() . ")");
                    break;
                case 'learner':
                default:
                    $pl->filterByAttribute('open_courses_is_published', true);
                    $relationType = "learner";
                    $pl->filter(false, "ak_open_courses_course_learner_group_id IN (SELECT UserGroups.gID FROM UserGroups WHERE UserGroups.uID = " . $u->getUserID() . ")");
                    break;
            }

            if ($this->get('searchQuery' . $bID) !== '') {
                $pl->filterByName($this->get('searchQuery' . $bID));
            }
            $courses = $pl->getPage();

            Loader::model('open_courses_learner_state_list', 'open_courses');
            $nh = Loader::helper('navigation');

            $coursesResult = array();
            foreach ($courses as $course) {
                $new = array();
                $new['link'] = $nh->getLinkToCollection($course);
                $new['is_published'] = $course->getAttribute('open_courses_is_published');
                $new['title'] = $course->getCollectionName();
                if ($relationType == "learner") {
                    // get progess: 
                    $progressArr = OpenCoursesLearnerStateList::getProgressForLearner($course->cID, $u->uID);
                    $new['progressPercentage'] = 0; // prevent division by zero
                    if ($progressArr['total'] > 0) {
                        $new['progressPercentage'] = round(($progressArr['completed'] / $progressArr['total']) * 100);
                    }
                } else {
                    // 2DO: calculate learner overall progress or do it in separate view
                    // right now it is not visible
                }
                $coursesResult[] = $new;
            }


            $this->set('courses', $coursesResult);
            $this->set('coursesPageList', $pl);

            $paginator = $pl->getPagination();
            $this->set('paginator', $paginator);

            // search parameters:
            $this->set('relationType', $relationType);
            $this->set('searchQuery', $this->get('searchQuery' . $bID));

            // leave other form vars intact
            $form_vars = array('searchQuery' . $bID, 'relationType' . $bID);
            $hiddenFields = array();
            foreach ($this->get(NULL) as $name => $value) {
                if (in_array($name, $form_vars))
                    continue;
                $name = htmlspecialchars($name);
                $value = $value; //2DO: sanitize this? can also be an array... 
                $hiddenFields[$name] = $value;
            }
            $this->set('hidden_fields', $hiddenFields);

            // course creation permissions
            // can be configured in concrete5 dashboard
            $och = Loader::helper('open_courses', 'open_courses');
            $userCanCreateCourse = $och->can_user_create_course($u);
            $this->set('userCanCreateCourse', $userCanCreateCourse);
        }
    }

    public function action_create_blank() {
        
        $u = new User();
        $loggedIn = $u->isLoggedIn();
        $och = Loader::helper('open_courses', 'open_courses');
        $userCanCreateCourse = $och->can_user_create_course($u);
        if ($this->isPost() && $u->isLoggedIn() && $userCanCreateCourse) {
            $title = $this->post('title');
            $och = Loader::helper('open_courses','open_courses');
            $title = $och->sanitize_string($title);
            if ($title === "")
                $title = t("New blank course");

            Loader::model('open_courses_course_setup', 'open_courses');
            $courseSetup = new OpenCoursesCourseSetup();
            $course = $courseSetup->createBlankCourse($title);
            $courseSetup->addTeacherToCourse($u->getUserID()); // 2DO: check for return errors?
            $this->redirect($course->getCollectionPath());
        }
    }

    public function action_upload_course() {
        $u = new User();
        $loggedIn = $u->isLoggedIn();
        $och = Loader::helper('open_courses', 'open_courses');
        $userCanCreateCourse = $och->can_user_create_course($u);
        $createError = "";
        if ($this->isPost() && $u->isLoggedIn() && $userCanCreateCourse) {

            $och = Loader::helper('open_courses', 'open_courses');
            if (!isset($_FILES['file']) || !($_FILES['file']['size'] > 0)) {
                $createError = t('No file selected. Please select a zip-file!');
            } elseif (($val_error = $och->validate_course_zip_file($_FILES['file'])) !== true) {
                $createError = $val_error;
            }

            if ($createError === "") {
                $file = $_FILES['file'];
                $path = $_FILES['file']["tmp_name"];

                Loader::Model('open_courses_course_setup', 'open_courses');
                $courseSetup = new OpenCoursesCourseSetup();
                $course = $courseSetup->createFromImport($path);
                $courseSetup->addTeacherToCourse($u->getUserID());
                if ($courseSetup->hasError()) {
                    $createError = $courseSetup->getError();
                } else {
                    // 2DO: where do we show the import log?
                    // save it somewhere? (attribute?)
                    // $this->set('log', $courseSetup->getLog());
                    $this->redirect($course->getCollectionPath());
                }
            }
        } else {
            $createError = t('You are not logged in or you do not have permission to execute this operation');
        }

        $this->set('createError', $createError);
    }

}
