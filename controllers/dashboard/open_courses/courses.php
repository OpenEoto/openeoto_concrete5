<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('crud_controller', 'open_courses');

class DashboardOpenCoursesCoursesController extends CrudController {

    protected $pkg_handle = 'open_courses';
    protected $entity = 'course';

    public function on_start() {
        $this->error = Loader::helper('validation/error');
    }

    public function on_before_render() {
        $this->set('error', $this->error);
    }

    public function view($filterByCourseID = NULL) {

        if (!ENABLE_APPLICATION_EVENTS) {
            $this->error->add(t('Please enable Application Events, otherwise this plugin will not work correctly!'));
        }

        if ($this->isPost()) {
            switch ($this->post('action')) {
                case 'add_blank_course':

                    $title = $this->post('title'); // 2DO: sanitize string
                    Loader::model('open_courses_course_setup', 'open_courses');
                    $courseSetup = new OpenCoursesCourseSetup();
                    $newCourse = $courseSetup->createBlankCourse($title);
                    if (!$newCourse) {
                        $this->set('error', $newCourse->getError());
                    } else {
                        $this->set('success', t('Course added succesfully'));
                        $filterByCourseID = $newCourse->getCollectionID();
                    }

                    break;
            }
        }

        $this->set('filterByCourseID', $filterByCourseID);
        // pagelist checks for permissions automatically
        $pl = new PageList();
        $pl->filterByOpenCoursesIsIndex(0);
        $pageType = CollectionType::getByHandle('open_courses_course');
        $pl->filterByCollectionTypeID($pageType->ctID);
        $pl->setItemsPerPage(25);

        if ($filterByCourseID !== NULL) {
            $pl->filter('p1.cID', $filterByCourseID);
        }
        $this->set('list', $pl);
        $this->set('results', $pl->getPage());

        $this->render('list');
    }

    public function import() {
        if ($this->isPost()) {
            $this->validate('add');
            if (!$this->error->has()) {

                switch ($this->post('action')) {
                    case 'fileManager':
                        $fileObject = File::getByID($this->post('fID'));
                        $path = $fileObject->getPath();
                        breaK;
                    case 'upload':
                        $file = $_FILES['file'];
                        $path = $_FILES['file']["tmp_name"];
                        break;
                }

                Loader::Model('open_courses_course_setup', 'open_courses');

                $courseImport = new OpenCoursesCourseSetup();
                $course = $courseImport->createFromImport($path);

                if ($courseImport->hasError()) {
                    $this->error->add($courseImport->getError());
                } else {
                    
                    $this->set('success', t('Course imported successful!'));
                    $this->set('importSuccess', true);
                    $this->set('courseID', $course->getCollectionID());
                    $this->set('log', $courseImport->getLog());
                }
            }
        }
        $this->render('import');
    }

    public function teachers($courseID) {

        $course = Page::getByID($courseID); // 2DO: check if exists
        $this->set('courseID', $courseID);

        $groupID = $course->getAttribute('open_courses_course_teacher_group_id');
        if (empty($groupID)) {
            throw new Exception(t('Group-ID for teacher user group not found in database.'));
            exit;
        }

        Loader::model('user_list');
        $ul = new UserList();
        $ul->filterByGroupID($groupID);
        $this->set('list', $ul);
        $this->set('results', $ul->getPage());

        $this->render('teachers');
    }

    public function add_teacher($courseID) {
        if ($this->isPost()) {
            Loader::model('open_courses_course', 'open_courses');
            $course = OpenCoursesCourse::getByID($courseID);
            $userID = $this->post('userID');
            $course->addTeacherToCourse($userID); // 2DO: check success / catch errors?
            $this->redirect('teachers/' . $courseID);
        }
    }

    public function remove_teacher($courseID, $userID) {
        Loader::model('open_courses_course', 'open_courses');
        $course = OpenCoursesCourse::getByID($courseID);
        $course->removeTeacherFromCourse($userID);
        $this->redirect('teachers/' . $courseID);
    }

    public function delete_check($cIDd, $name) {
        $this->set('remove_name', $name);
        $this->set('remove_cid', $cIDd);
        $this->render('delete_check');
    }

    public function delete($cIDd, $name) {

        $course = Page::getByID($cIDd);
        if ($course->cID === NULL) {
            $this->error->add(t('Error: Course could not be found.'));
            return;
        }

        $course->delete(); // event in page type  controller will be triggered and remove everything
        $this->set('message', t('Course was deleted successfully.'));
        $this->view();
    }

    protected function validate($task = 'add') {

        // validation used only for add task
        if ($task == 'add') {

            switch ($this->post('action')) {
                case 'fileManager':
                    $fID = $this->post('fID');
                    if (empty($fID)) {
                        $this->error->add(t('No file selected. Please select a .zip file'));
                    } else {
                        $fileObject = File::getByID($fID);
                        $filePath = $fileObject->getPath();
                        $fileParts = pathinfo($filePath);
                        if ($fileParts['extension'] !== "zip") {
                            $this->error->add(t('Invalid file. Please use a .zip file!'));
                        }
                    }
                    break;

                case 'upload':

                    $och = Loader::helper('open_courses', 'open_courses');

                    if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {
                        // 2DO: get max filesize from config? (second argument
                        if (($val_error = $och->validate_course_zip_file($_FILES['file'])) !== true) {
                            $this->error->add($val_error);
                        }
                    } else {
                        $this->error->add(t('No file selected. Please select a zip-file!'));
                    }

                    break;
            }
        }
    }

}
