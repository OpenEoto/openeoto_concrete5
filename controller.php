<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

class OpenCoursesPackage extends Package {

    protected $pkgHandle = 'open_courses';
    protected $appVersionRequired = '5.6';
    protected $pkgVersion = '0.9.2';

    public function getPackageDescription() {
        return t('Course management for Concrete5');
    }

    public function getPackageName() {
        return 'OpenEoto Courses';
    }

    public function on_start() {
        // Hooking into events:
        // http://www.concrete5.org/documentation/developers/system/events
        // Page Type Events for courses and sessions
        // We activativate events here for the page type, so we can use them in the pagetype controller
        // we need unfortunately both because of http://www.concrete5.org/community/forums/customizing_c5/on_page_delete-not-firing/
        Events::extendPageType('open_courses_course', 'on_page_delete');
        Events::extendPageType('open_courses_course', 'on_page_move');

        Events::extendPageType('open_courses_session', 'on_page_delete');
        Events::extendPageType('open_courses_session', 'on_page_move');

        // these pagetype events will fire the open courses event because of a more convenient way to manage events...
        // general c5 events
        $filename = DIRNAME_PACKAGES . '/' . $this->pkgHandle . '/models/open_courses_events.php';
        Events::extend('on_user_delete', 'open_courses_events', 'on_user_delete', $filename);

        //package events
        $packageEvents = array(
            'on_course_delete',
            'on_session_delete',
            'on_course_learner_add',
            'on_course_learner_remove'
        );
        foreach ($packageEvents as $eventHandle) {
            Events::extend('open_courses_' . $eventHandle, 'openCoursesEvents', $eventHandle, $filename);
        }
    }

    public function uninstall() {
        // 2DO: remove files folder (files/open_courses/) - is not used anymore?
        // 2DO: remove teacher uploaded files of course? (difficult, because maybe files are used across courses?)
        parent::uninstall();
    }

    public function upgrade() {
        parent::upgrade();
        $pkg = Package::getByHandle('open_courses');
        $this->installComponents($pkg);
    }

    public function install() {
       
        if (PERMISSIONS_MODEL != 'advanced') {
            $message = t("Please enable Advanced Permissions for this add-on.");
            throw new Exception($message);
            exit;
        }
        
        $pkg = parent::install();

        Loader::model('collection_types');
        Loader::model('single_page');
        Loader::model('attribute/categories/collection');
        Loader::model('page_list');

        $this->installComponents($pkg);

        // initial installation procedure
        // add teacher group (for dashboard access)
        $groupName = t('Course teachers');
        if (Group::getByName($groupName) === NULL) {
            $group = Group::add($groupName, t('Users who administrate courses'));
            $pkg->saveConfig('OPEN_COURSES_TEACHER_GROUP_ID', $group->getGroupID());
        } else { // 2DO: is the following really neccessary?
            $g = Group::getByName($groupName);
            $pkg->saveConfig('OPEN_COURSES_TEACHER_GROUP_ID', $g->getGroupID());
        }


        // install pages
        // dashboard single pages
        $p = SinglePage::add('/dashboard/' . $this->pkgHandle, $pkg);
        $p->update(array('cDescription' => $this->getPackageDescription()));
        $p = SinglePage::add('/dashboard/' . $this->pkgHandle . '/overview', $pkg);
        $p->update(array('cName' => t('Overview')));
        $p = SinglePage::add('/dashboard/' . $this->pkgHandle . '/courses', $pkg);
        $p->update(array('cName' => t('Course Management')));
        $p = SinglePage::add('/dashboard/' . $this->pkgHandle . '/settings', $pkg);
        $p->update(array('cName' => t('Settings')));


        // Add page types
        $pageType = CollectionType::getByHandle('open_courses_course');
        if (!$pageType || !intval($pageType->getCollectionTypeID())) {
            $pageType = CollectionType::add(array('ctHandle' => 'open_courses_course', 'ctName' => t('Course')), $pkg);
        }

        $pageType = CollectionType::getByHandle('open_courses_session');
        if (!$pageType || !intval($pageType->getCollectionTypeID())) {
            $pageType = CollectionType::add(array('ctHandle' => 'open_courses_session', 'ctName' => t('Course session')), $pkg);
        }

        // Add head section pages for each page type (if there aren't any)
        $parent_pages = array(
            'course' => t('Open Courses Summary'));

        foreach ($parent_pages as $page_type_handle => $title) {
            // create page with page type + is_index TRUE!
            $parentPage = Page::getByID(1);
            $data = array(
                'name' => $title,
                'cHandle' => strtolower($title),
                'cDescription' => t('Summary of ' . $title . 's')
            );
            $pt = CollectionType::getByHandle("open_courses_" . $page_type_handle);
            $newPage = $parentPage->add($pt, $data);
            $newPage->setAttribute('open_courses_is_index', 1);
            $pkg->saveConfig('OPEN_COURSES_PAGE_' . strtoupper($page_type_handle) . '_ID', $newPage->cID);
        }


        // Set initial config values
        $pkg->saveConfig('OPEN_COURSES_SETTINGS_CREATE_MODE', 'admin');
        $pkg->saveConfig('OPEN_COURSES_SETTINGS_IMPORT_ALLOW_SCRIPT_TAGS', true);
        $pkg->saveConfig('OPEN_COURSES_SETTINGS_THEME', 'open_courses'); // theme or new course/session pages
        /* possible values:
         * 'everybody' => all registered users can create courses (beware - file uploading)
         * 'teacher' => all users which are in general group of teachers can create new courses
         * 'admin' (default, only people who can access conrete5 dashboard and open courses section can create courses
         */

        return true;
    }

    /*
     * easily changeable installation of components (attributes & co)
     * IMPORTANT: always check here if element exists because this method is executed on every install/upgrade action!
     */

    public function installComponents($pkg) {

        // install theme:
        $theme = PageTheme::getByHandle("open_courses");
        if (is_null($theme)) {
            $theme = PageTheme::add("open_courses", $pkg);
        }

        Loader::model('collection_types');
        Loader::model('attribute/categories/collection');

        if (Config::get('OPEN_COURSES_PAGE_ID_COURSE_DASHBOARD') === NULL) {
            $p = SinglePage::add('/course_dashboard', $pkg);
            $p->update(array('cName' => t('Course Dashboard')));
            $p->setAttribute('exclude_nav', 1);
            $p->setTheme($theme);
            $pkg->saveConfig('OPEN_COURSES_PAGE_ID_COURSE_DASHBOARD', $p->cID);
        }

        // install block types
        $blockTypes = array('open_courses_my_courses', 'open_courses_join_course', 'open_courses_content');
        foreach ($blockTypes as $btHandle) {
            $bt = BlockType::getByHandle($btHandle);
            if (!is_object($bt)) {
                BlockType::installBlockTypeFromPackage($btHandle, $pkg);
            }
        }

        // attributes
        $keySet = AttributeSet::getByHandle('open_courses');
        if ($keySet === NULL || (bool) $keySet->error) {
            // add attribute set open_courses
            $eaku = AttributeKeyCategory::getByHandle('collection');
            $eaku->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_SINGLE);
            $keySet = $eaku->addSet($this->pkgHandle, t('Open Courses'), $pkg);
        }

        // attribute types
        $select = AttributeType::getByHandle('select');
        $image_file = AttributeType::getByHandle('image_file');
        $bool = AttributeType::getByHandle('boolean');
        $number = AttributeType::getByHandle('number');
        $text = AttributeType::getByHandle('text');
        $textarea = AttributeType::getByHandle('textarea');
        $date_time = AttributeType::getByHandle('date_time');

        $attributes = array(
            // we need head pages for the tree, so we need an attribute to determine this:
            'is_index' => array('type' => $bool, 'name' => t('Index?'), 'searchable' => false), // general section attribute, we can determine via page type which page it is...
            // general:
            'is_published' => array('type' => $bool, 'name' => t('Published?'), 'searchable' => false),
            //2DO documentate that
            //2DO: 'license'=>
            //2DO: 'author'=>
            // COURSES:
            'course_completion_approval_needed' => array('type' => $bool, 'name' => t('Course: Completion Approval needed?'), 'searchable' => false),
            'course_code' => array('type' => $text, 'name' => t('Course: Invite code'), 'searchable' => false),
            'course_learner_group_id' => array('type' => $number, 'name' => t('Course: Group ID Learners'), 'searchable' => false),
            'course_teacher_group_id' => array('type' => $number, 'name' => t('Course: Group ID Teachers'), 'searchable' => false),
            'course_file_set_id' => array('type' => $number, 'name' => t('Course: File Set ID'), 'searchable' => false),
            'course_stat_learner_left_count' => array('type' => $number, 'name' => t('Course: Learner left count'), 'searchable' => false),
            // SESSIONS
            'session_id' => array('type' => $number, 'name' => t('Session id'), 'searchable' => false), // 2DO: implement unique check for id from config.json
        );

        foreach ($attributes as $handle => $properties) {
            if (!is_object(CollectionAttributeKey::getByHandle($this->pkgHandle . '_' . $handle))) {
                CollectionAttributeKey::add($properties['type'], array('akHandle' => $this->pkgHandle . '_' . $handle, 'akName' => $properties['name'], 'akIsSearchable' => $properties['searchable']), $pkg)->setAttributeSet($keySet);
            }
        }
        // wheter or not a teacher must confirm a session completion by learner, can be used in course and session (overwrite by session?)
        $completionApprovalSelectAttribute = CollectionAttributeKey::getByHandle('open_courses_session_completion_approval_needed');

        // Add attribute
        if (!is_object($completionApprovalSelectAttribute)) {

            CollectionAttributeKey::add($select, array('akHandle' => 'open_courses_session_completion_approval_needed',
                'akName' => t('Session: Completion approval needed?'),
                'akIsSearchable' => false,
                'akIsSearchableIndexed' => false,
                'akSelectAllowMultipleValues' => false,
                'akSelectAllowOtherValues' => false,
                'akSelectOptionDisplayOrder' => 'display_asc', // alpha_asc or popularity_desc
                    ), $pkg)->setAttributeSet($keySet);

            //Add option values
            $completionApprovalSelectAttribute = CollectionAttributeKey::getByHandle('open_courses_session_completion_approval_needed');
            SelectAttributeTypeOption::add($completionApprovalSelectAttribute, 'course_setting');
            SelectAttributeTypeOption::add($completionApprovalSelectAttribute, 'yes');
            SelectAttributeTypeOption::add($completionApprovalSelectAttribute, 'no');
        }

        // add file attributes:
        // thanks to: http://www.concrete5.org/community/forums/customizing_c5/install-custom-attributes-with-a-custom-block-type/

        Loader::model('file_attributes');

        $keySet = AttributeSet::getByHandle('open_courses_files');
        if ($keySet === NULL || (bool) $keySet->error) {
            // add attribute set "open_courses" for files
            $eaku = AttributeKeyCategory::getByHandle('file');
            //$eaku->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_SINGLE); // 2DO. what does this mean exactly?
            $keySet = $eaku->addSet("open_courses_files", t('Open Courses'), $pkg);
        }
        $cat = AttributeKeyCategory::getByHandle('file');
        // this attribute is used to prevent duplicate file imports
        if (!is_object(FileAttributeKey::getByHandle($this->pkgHandle . '_' . "files_import_path_md5"))) {
            $key = FileAttributeKey::add($text, array(
                        'akHandle' => $this->pkgHandle . '_' . "files_import_path_md5",
                        'akName' => 'Open Courses: Import hash',
                        'akIsSearchable' => 1, // file manager searchable
                        'akIsSearchableIndexed' => 1, // included in indexed search
                            ), $pkg)->setAttributeSet($keySet);
        }
    }

}
