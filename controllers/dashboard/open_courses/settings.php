<?php

defined('C5_EXECUTE') or die(_("Access Denied."));
Loader::library('crud_controller', 'open_courses');

// thanks to: https://www.concrete5.org/documentation/how-tos/developers/use-the-config-table-for-configuration-data/

class DashboardOpenCoursesSettingsController extends CrudController {

    protected $pkg_handle = 'open_courses';
    protected $pkg;
    protected $entity = 'course';
    protected $configKeys = array(
        'OPEN_COURSES_SETTINGS_CREATE_MODE',
        'OPEN_COURSES_SETTINGS_IMPORT_ALLOW_SCRIPT_TAGS',
        'OPEN_COURSES_SETTINGS_THEME'
    );

    public function on_start() {
        $this->error = Loader::helper('validation/error');
        $this->pkg = Package::getByHandle($this->pkg_handle);
    }

    public function on_before_render() {
        $this->set('error', $this->error);
    }

    public function view() {
        $this->loadSettings();
    }

    public function edit() {



        if ($this->isPost()) {


            if (($validData = $this->validateSettings()) === FALSE) {

                foreach ($this->configKeys as $key) {
                    $this->set($key, $this->post($key));
                }

                $this->render('edit');
                return;
            }

            $this->pkg->saveConfig('OPEN_COURSES_SETTINGS_THEME', $validData['OPEN_COURSES_SETTINGS_THEME']);
            $this->pkg->saveConfig('OPEN_COURSES_SETTINGS_CREATE_MODE', $validData['OPEN_COURSES_SETTINGS_CREATE_MODE']);
            $this->pkg->saveConfig('OPEN_COURSES_SETTINGS_IMPORT_ALLOW_SCRIPT_TAGS', (bool) $validData['OPEN_COURSES_SETTINGS_IMPORT_ALLOW_SCRIPT_TAGS']);

            $this->set('message', t('Configuration settings saved.'));
            $this->view();
        } else {
            $this->loadSettings();
            $this->render('edit');
        }
    }

    protected function validateSettings() {
        $theme = PageTheme::getByHandle($this->post('OPEN_COURSES_SETTINGS_THEME')); // 2DO: check ID !== NULL
        if ($theme === NULL) {
            $this->error->add('Error: Theme not found.');
            return FALSE;
        }

        $och = Loader::helper('open_courses', 'open_courses');
        $validData = array();
        foreach ($this->configKeys as $key) {
            $validData[$key] = $och->sanitize_string($this->post($key));
        }
        return $validData;
    }

    protected function loadSettings() {


        foreach ($this->configKeys as $key) {
            $this->set($key, $this->pkg->config($key));
        }
    }

    /*
     * give teacher group to permissions to access the dashboard bar (necessary for c5 built-in editing used in session editing)
     */

    public function setup_permissions() {
        
        $pkg = Package::getByHandle('open_courses');

        // general teacher group
        $teacherGroup = Group::getByID($pkg->config('OPEN_COURSES_TEACHER_GROUP_ID')); // 2DO: check if exists (wich return value?)
        // we assume there is an administrator group
        $adminGroup = Group::getByName("Administrators"); // returns null if not exist
        if ($this->isPost()) {

            // page permissions:
            // teacher group needs access to dashboard, but not full access
            // every subpage of dashboard/ must be set to manual override and cleared of teacher group access
            // thanks to: http://www.concrete5.org/community/forums/customizing_c5/programmatically_setting_advanced_permissions_example/#428743
            // as soon as we call assignPermissions, all preexisting permissions will be cleared -> only if page was not set to manual override before!
            // prepare all permission keys
            Loader::model('page');
            $allPagePermissionKeys = PermissionKey::getList('page');
            $allPagePermissionKeyHandles = array_map(function($permissionKey) {
                return $permissionKey->getPermissionKeyHandle();
            }, $allPagePermissionKeys);


            $dashboardPage = Page::getByPath('/dashboard');
            if ($dashboardPage->cID === NULL) {
                throw new Exception(t("Dashboard page not found."));
            }

            if ($adminGroup !== NULL) {
                $dashboardPage->assignPermissions($adminGroup, $allPagePermissionKeyHandles);
            }

            $dashboardPage->assignPermissions($teacherGroup, array(
                'view_page'
            ));

            // these are the system pages where teacher group should not have access by default
            // they are by default on "get permissions of parent page", so we have to change it now:
            $childIds = $dashboardPage->getCollectionChildrenArray(true);
            foreach ($childIds as $cID) {
                $page = Page::getByID($cID);
                if ($page->cID !== NULL) {
                    $page->setPermissionsToManualOverride(); // this will clear all preexisting permissions, but only if the page was not set to manual override before
                    $page->clearPagePermissions();
                    if ($adminGroup !== NULL) {
                        $page->assignPermissions($adminGroup, $allPagePermissionKeyHandles);
                    }
                }
            }
                        
            $this->set('message',t('Setup permissions for dashboard pages finished. Please note that you need the set some permissions manually. See Installation Guide for detailed information.'));
            $this->view();
            
        }
        else{
            $this->render('setup_permissions');
        }
    }

}
