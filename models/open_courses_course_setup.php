<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

// 2DO: turn this into a job because of max execution time?
/*
 * class for creating blank courses or importing courses via .zip archive
 */
class OpenCoursesCourseSetup {

    private $error = NULL;
    private $base_dir;
    private $log = ""; // html log for warnings and notices
    private $debug = false; // will put everything in log
    private $course;

    public function __construct() {
        $this->base_dir = DIR_BASE . '/files/open_courses';
        $this->base_url = BASE_URL . '/files/open_courses';
    }

    /*
     * create a blank course
     */

    public function createBlankCourse($title = 'New course') {
        if ($title == "" || $title === NULL) {
            $title = "New course";
        }
        $this->course = $this->createCoursePage($title);
        Events::fire('open_courses_on_course_add', $this->course);
        return $this->course;
    }

    /*
     * import via zip file
     */

    public function createFromImport($filePath) {

        $this->log(t('Start course import...'));
        $this->logDebug('FilePath: ' . $filePath);

        if (!$this->checkDirectoryPermissions()) {
            $this->setError(t('Directory structure could not be set up. File Permission problem?'));
            return false;
        }

        // ============ zip file & directory/file validation =============
        // create temporary directory
        $dir = $this->tempdir($this->base_dir . '/tmp/');

        // 2DO: error handling for zip
        $zip = new ZipArchive;
        $zip->open($filePath);
        $zip->extractTo($dir);
        $zip->close();

        // search for course.json in first and second level directory
        // determine if there is a parent folder or not
        $course_dir = NULL;
        $files1 = scandir($dir);
        $this->logDebug("Scanning directory " . $dir);
        $this->logDebug(print_r($files1, true));
        if (in_array("course.json", $files1)) {
            $course_dir = $dir;
        } else {
            // second level
            // find directories and scan inside them
            foreach ($files1 as $path) {
                if ($path !== "." && $path !== ".." && is_dir($dir . "/" . $path)) {
                    $this->logDebug("Scanning directory " . $dir . "/" . $path);
                    $files2 = scandir($dir . "/" . $path);
                    $this->logDebug(print_r($files2, true));
                    if (in_array("course.json", $files2)) {
                        $course_dir = $dir . "/" . $path; // set subdirectory as main course directory
                        $this->logDebug("Setting course directory to " . $dir . "/" . $path);
                        break;
                    }
                }
            }
        }
        if ($course_dir === NULL || !is_dir($course_dir)) {
            $this->setError(t('Directory structure of extracted zip archive is not valid or it is not a directory. Could not find sessions/-folder and index.html file.'));
            $this->removeDirectory($dir);
            return false;
        }

        // read course.json file
        $courseSettings = json_decode(file_get_contents($course_dir . '/course.json'));
        if ($courseSettings === NULL) {
            $this->setError(t('JSON file course.json could not be parsed. Error:') . json_last_error());
            $this->removeDirectory($dir);
            return false;
        }
        // 2DO: check for required fields in json file?
        $course_title = property_exists($courseSettings, 'title') ? $courseSettings->title : "Undefined";
        $course_description = property_exists($courseSettings, 'description') ? $courseSettings->description : "";

        $session_files = array(); // html files which represent a session
        $sessions = $courseSettings->sessions;
        if (count($sessions) == 0) {
            $this->setError(t('Could not find any session .html files in directory sessions.'));
            $this->removeDirectory($dir);
            return false;
        }

        // ============ EO zip file & directory/file validation =============
        // ======================== page generation =========================
        $och = Loader::helper('open_courses', 'open_courses');

        // CREATE NEW PAGE
        $coursePage = $this->createCoursePage($course_title, $course_description); // 2DO: error handling?
        if ($coursePage === FALSE) {
            return FALSE;
        }
        $course_code = $coursePage->getAttribute("open_courses_course_code");
        $file_set = FileSet::getByID($coursePage->getAttribute('open_courses_course_file_set_id'));

        /* IMPORT SESSIONS */
        $dh = Loader::helper('date');
        Loader::library('SmartDOMDocument.class', 'open_courses');
        Loader::library("file/importer");
        Loader::model('file_list');


        foreach ($sessions as $s) {

            $title = property_exists($s, 'title') ? $s->title : "Unnamed session";
            $title = $och->sanitize_string($title);
            $description = property_exists($s, 'description') ? $s->description : "";
            $description = $och->sanitize_string($description);

            $id = property_exists($s, 'id') ? $s->id : FALSE; // 2DO: autogenerate a unique value? (if we want to have auto-updates in future, this value is required)
            $path = property_exists($s, 'path') ? $s->path : FALSE;
            $license_path = property_exists($s, 'license') ? $s->license : FALSE;

            if (!$path) {
                $this->log(t("Path for session not found. Please check your course.json file. Session: ") . $title);
                continue;
            }

            $html = file_get_contents($course_dir . '/' . $path);
            if (!$html) {
                $this->log(t('File not found for session. Path: ') . $path);
                continue;
            }

            // REPLACE INTERNAL LINKS & IMPORT MEDIA
            // IMPORTANT: THIS WILL ONLY IMPORT FILES WHICH ARE USED IN HTML FILES, UNCONNECTED FILES WILL NOT BE IMPORTED
            // thanks to: http://darkblue.sdf.org/weblog/the-wonder-of-domdocument.html
            $media_folder_url = $this->base_url . '/media/' . $course_code;
            $media_folder_url = rtrim($media_folder_url, '/'); // Remove trailing slash
            // Instantiate the object
            $doc = new SmartDOMDocument();
            // Build the DOM from the input (X)HTML snippet

            $doc->loadHTML($html);

            // check if there is a body element
            // if it is there, only get content of body element (without head e.g.)
            $body = $doc->getElementsByTagName('body');
            if ($body && $body->length > 0) {
                $body = $body->item(0);
                $bodyHtml = $doc->savehtml($body);
                $doc = new SmartDOMDocument();
                $doc->loadHTML($bodyHtml);
            }

            try {

                // html elements which can contain relative links
                $replace_elements = array(
                    "img" => "src",
                    "source" => "src",
                    "a" => "href"
                );

                $fp = FilePermissions::getGlobal();
                $fh = Loader::helper('file');

                foreach ($replace_elements as $tag => $attr) {
                    $els = $doc->getElementsByTagName($tag);
                    foreach ($els as $el) {
                        $url = $el->getAttribute($attr);
                        $url_parts = parse_url($url);
                        // check if there are relative links
                        // 2DO: check if media/ is involved? (right now the detection is buggy)
                        // skip if it not in media/folder
                        if (!isset($url_parts['host']) || ($url_parts['host'] == '')) {

                            // 2DO: potential security risk here?
                            // we strip everything from first "media/" occurence in url, e.g. "../media/" 
                            $file_path = substr($url, strpos($url, "media/") + strlen("media/"));
                            // our internal file system link to the unzipped folder
                            $full_file_path = $course_dir . '/media/' . $file_path; //2DO: check if file exists
                            // we hash the file_path to prevent double uploads to the file manager
                            $file_path_hash = md5($file_path);

                            // 2DO: should we really sort this out? or do people upload html example files?
                            // 2DO: by default html/htm files are not supported by concrete5
                            // 2DO: docs - better use zip?
                            // special rule: html and htm links will not be imported (could be links to other sessions)
                            // right now linking between sessions does not work, but could be implemented in future
                            $file_name = basename($full_file_path);
                            $file_extension = $fh->getExtension($file_name);
                            if (in_array($extension, array('html', 'htm'))) {
                                // 2DO: is this really import for the user?
                                // 2DO: translate this
                                $this->log('File was skipped because it is html/htm, could be relative links? (' . $url . ')');
                                continue;
                            }

                            // check if file is in folder
                            if (file_exists($full_file_path)) {

                                // check if file is already in file manager
                                $fl = new FileList();
                                $fl->filterBySet($file_set);
                                $fl->filterByAttribute('open_courses_files_import_path_md5', $file_path_hash, '=');
                                if ($fl->getTotal() > 0) {
                                    // file already exists, use this one for replacing the link
                                    $files = $fl->get(1);
                                    $new_file_version_object = $files[0]->getRecentVersion();
                                } else {
                                    // file does not exist, import it
                                    $fi = new FileImporter();

                                    // check if it is allowed to import this file (permissions user)
                                    // and check wheter the file extension is allowed by concrete5
                                    // 2DO: document this restriction
                                    if (!$fp->canAddFileType($fh->getExtension($full_file_path))) {
                                        // 2DO: translation vsprintf
                                        $this->log('File ' . $url . ' could not be imported, because filetype is not allowed by system.');
                                        continue; // we continue the parsing
                                    }

                                    $new_file_version_object = $fi->import($full_file_path); // 2DO: error handling
                                    if (!$new_file_version_object instanceof FileVersion) {
                                        $this->log('File ' . $url . ' could not be imported: ' . $fi->getErrorMessage($new_file_version_object));
                                        continue;
                                    }
                                    $new_file_version_object->setAttribute('open_courses_files_import_path_md5', $file_path_hash);
                                    $file_set->addFileToSet($new_file_version_object->getFile());
                                }

                                $url = $new_file_version_object->getRelativePath();

                                // replace value in html
                                $el->setAttribute($attr, $url);
                            } else {
                                $this->log('File ' . $url . ' could not be imported, because file could not be found in directory.');
                            }
                        }
                    }
                }

                // remove all script tags (security measure, can be turend on in settings)
                $pkg = Package::getByHandle('open_courses');
                $allowScriptTags = (bool) $pkg->config('OPEN_COURSES_SETTINGS_IMPORT_ALLOW_SCRIPT_TAGS');
                if ($allowScriptTags !== TRUE) {
                    $els = $doc->getElementsByTagName("script");
                    foreach ($els as $el) {
                        $el->parentNode->removeChild($el);
                    }
                }

                $html = $doc->saveHTMLExact();
            } catch (Exception $e) {
                // 2DO: catch exceptions and show them as error report...
                // there is also a "not found error" when getElementsByTagName does not find something?!
                // should not be logged - how we can recognize this?
            }

            $data = array(
                'name' => $title,
                'cDescription' => $description
            );
            $pt = CollectionType::getByHandle("open_courses_session");
            $new_session = $coursePage->add($pt, $data);
            // add id from course file to it (attribute)
            if ($id !== FALSE) {
                $new_session->setAttribute('open_courses_session_id', $id);
            }

            // add main block with content from session file
            // we use a special block which has tinymce settings for audio/video built in
            // thanks to jordanlev: http://www.concrete5.org/community/forums/customizing_c5/programmatically-add-a-block-to-a-page-area/#367144
            $block = BlockType::getByHandle('open_courses_content');
            $data = array(
                'content' => $html,
            );
            $new_session->addBlock($block, 'Open-Courses-Content-Area', $data);
            $new_session->setAttribute('open_courses_is_published', true);
            $page_theme = $this->getConfiguredPageTheme();
            $new_session->setTheme($page_theme);

            // 2DO: add special block to save changes ? (necessary for update?)
        }

        // ===================== EO page generation ===========================
        // delete temp dir (extracted archive)
        $this->removeDirectory($dir);

        $this->log(t('Course import finished successful...'));

        $this->course = $coursePage;

        Events::fire('open_courses_on_course_add', $this->course);

        return $this->course;
    }

    /*
     * collection methods
     */

    /*
     * creates new a page (c5 collection) for course
     */

    protected function createCoursePage($title, $description = '') {

        // ******************
        // CREATE COURSE PAGE
        // ******************
        // unique course code (for media folder etc)
        $course_code = NULL;
        for ($i = 0; $i < 50; $i++) {
            $uniqid = uniqid();
            $pl = new PageList();
            $pl->filterByAttribute("open_courses_course_code", $uniqid);
            if ($pl->getTotal() == 0) {
                $course_code = $uniqid;
            }
        }
        if ($course_code === NULL) {
            $this->set->error(t('Course code could not be generated. Please try again or contact an administrator.'));
            $this->removeDirectory($dir);
            return false;
        }


        $pkg = Package::getByHandle('open_courses');
        $parentID = $pkg->config('OPEN_COURSES_PAGE_COURSE_ID'); // pageID of courses parentPage (created on package installation)
        $parentPage = Page::getByID($parentID); // 2DO: check if exists
        // create parent page for this course

        $dh = Loader::helper('date');


        $data = array(
            'name' => $title,
            'cDescription' => $description
        );
        $pt = CollectionType::getByHandle("open_courses_course");
        // add course page to parent page
        $course_page = $parentPage->add($pt, $data);


        $course_page->setAttribute("open_courses_course_code", $course_code);

        // create teacher group
        $groupName = t('Course teachers for') . ' ID: ' . $course_page->getCollectionID();
        $teacher_group = Group::add($groupName, t('Users who are teachers in course with page ID:') . $course_page->getCollectionID());
        $course_page->setAttribute('open_courses_course_teacher_group_id', $teacher_group->getGroupID());


        // create learner group
        $groupName = t('Course learners for') . ' ID: ' . $course_page->getCollectionID();
        $learner_group = Group::add($groupName, t('Users who are learners in course with page ID:') . $course_page->getCollectionID());
        $course_page->setAttribute('open_courses_course_learner_group_id', $learner_group->getGroupID());

        // set permission for teacher  and learnergroup
        // http://www.concrete5.org/community/forums/customizing_c5/programmatically_setting_advanced_permissions_example/#23267
        // $coursePage->clearPagePermissions could also be used, but right now we only ADD our permissions to the permissions to the inherited ones


        $allPagePermissionKeys = PermissionKey::getList('page');
        $allPagePermissionKeyHandles = array_map(function($permissionKey) {
            return $permissionKey->getPermissionKeyHandle();
        }, $allPagePermissionKeys);

        // Administrators can do everything:
        $course_page->assignPermissions(Group::getByName("Administrators"), $allPagePermissionKeyHandles);

        // remove guest access
        // this does not work correctly:
        //$coursePage->assignPermissions(Group::getByName("Guest"), $allPagePermissionKeyHandles, PagePermissionKey::ACCESS_TYPE_EXCLUDE);
        //$coursePage->assignPermissions(Group::getByName("Guest"),array());
        // learners can see the page

        $course_page->assignPermissions($learner_group, array(
            'view_page'
        ));

        // teacher can see the page
        $course_page->assignPermissions($teacher_group, array(
            'view_page',
            'edit_page_contents',
            'approve_page_versions' // add_subpage'
        ));

        // create fileset with custom permissions
        $file_set = FileSet::createAndGetSet("FileSet for Course ID: " . $course_page->cID, FileSet::TYPE_PUBLIC);

        $course_page->setAttribute('open_courses_course_file_set_id', $file_set->getFileSetID());


        // apply advanced permissions to fileset
        // https://www.concrete5.org/community/forums/customizing_c5/assigning-file-set-permissions-for-a-group-programatically/
        // Add the permissions for the File Set
        $file_set->resetPermissions();
        //$file_set->acquireBaseFileSetPermissions(); // 2DO: necessary?
        $allFileSetPermissionKeys = PermissionKey::getList('file_set');
        $allFileSetPermissionKeyHandles = array_map(function($permissionKey) {
            return $permissionKey->getPermissionKeyHandle();
        }, $allFileSetPermissionKeys);

        // Administrators can do anything to the file set
        // 2DO: docs - admin group should not be deleted or applied to something else...
        $file_set->assignPermissions(Group::getByName('Administrators'), $allFileSetPermissionKeyHandles);

        // Exclude guests
        // $file_set->assignPermissions(Group::getByName('Guest'), $allFileSetPermissionKeyHandles, FileSetPermissionKey::ACCESS_TYPE_EXCLUDE);
        // teacher sof course can do everything
        $file_set->assignPermissions($teacher_group, array(
            'view_file_set_file',
            'search_file_set',
            'add_file',
            'edit_file_set_file_properties',
            'edit_file_set_permissions'
        ));

        // learner group can see files
        $file_set->assignPermissions($learner_group, array(
            'view_file_set_file'
        ));

        // set theme for course page

        $page_theme = $this->getConfiguredPageTheme();
        $course_page->setTheme($page_theme);


        return $course_page;
    }

    /*
     * error handling methods
     */

    public function hasError() {
        if ($this->error === NULL)
            return false;
        return true;
    }

    public function getError() {
        return $this->error;
    }

    private function setError($errorMessage) {
        $this->error .= $errorMessage;
    }

    /* user management methods */

    public function addTeacherToCourse($userID) {
        if ($this->course !== NULL) {
            Loader::model('open_courses_course', 'open_courses');
            $course = OpenCoursesCourse::getByID($this->course->getCollectionID());
            $course->addTeacherToCourse($userID);
        } else {
            // 2DO: throw error message, course not created yet?   
        }
    }

    /*
     * set up and check directory structure
     */

    public function checkDirectoryPermissions() {
        $directories = array(
            DIR_BASE . '/files/open_courses',
            DIR_BASE . '/files/open_courses/tmp',
            DIR_BASE . '/files/open_courses/media' // 2DO: is this still used? (we use file manager right now)
        );
        foreach ($directories as $dir) {
            if (!$this->createAndOrCheckDirectory($dir)) {
                return false;
            }
        }
        return true;
    }

    private function createAndOrCheckDirectory($dir) {
        if (!file_exists($dir) and !is_dir($dir)) {
            if (!mkdir($dir)) {
                $this->setError(t('Directory ' . $dir . ' could not be created. Please create it manually or check permissions on your webserver.'));
                return false;
            }
            if (!is_writable($dir)) {
                $this->setError(t('Directory ' . $dir . ' is not writable. Please check permissions on your webserver.'));
                return false;
            }
        }
        return true;
    }

    // http://de.php.net/manual/en/function.tempnam.php
    private function tempdir($dir = false) {
        $tempfile = tempnam($dir, '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }
        mkdir($tempfile);
        if (is_dir($tempfile)) {
            return $tempfile;
        }
    }

    // http://php.net/manual/es/function.rmdir.php
    /*
     * remove a directory with files in it
     */
    public function removeDirectory($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDirectory("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    protected function logDebug($line) {
        if ($this->debug) {
            $this->log($line);
        }
    }

    protected function log($line) {
        $this->log .= $line . " \n";
    }

    public function getLog() {
        return $this->log;
    }

    protected function getConfiguredPageTheme() {
        $och = Loader::helper('open_courses', 'open_courses');
        return $och->get_configured_page_theme();
    }

}
