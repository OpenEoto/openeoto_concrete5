<?php

class OpenCoursesHelper {
    /* taks an object of $_FILES 
     * 
     * @return true or error message
     */

    // 2DO: Get Max Size from Server Settings?
    public function validate_course_zip_file($file, $max_size_mb = false) {

        $vi = Loader::Helper('validation/file');

        if (!$vi->extension($file['name'], array('zip'))) {
            return t('File must be a .zip-archive.');
        }

        // 2DO: get from php? ini_get('upload_max_filesize'); - because we can't be bigger
        if (!$max_size_mb) {
            $max_size_bytes = $this->return_bytes(ini_get('post_max_size'));
        } else {
            $max_size_bytes = $max_size_mb * 1024 * 1024; // in bytes
        }
        if ($file['size'] > $max_size_bytes) {
            return t('The allowed maxium upload file size is ' . $max_size_mb . ' MB.');
        }

        return true;
    }

    // thanks to: http://stackoverflow.com/questions/4177455/how-can-i-get-max-file-upload-in-bytes
    public function return_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    public function can_user_create_course($user) {

        if(!$user->isLoggedIn()){
            return false;
        }

        $pkg = Package::getByHandle('open_courses');
        $createMode = $pkg->config("OPEN_COURSES_SETTINGS_CREATE_MODE");

        switch ($createMode) {
            case 'everybody':
                return true;
                break;
            case 'teacher':
                // check if user is in general teacher group:
                $userIsTeacher = $user->inGroup(Group::getByID($pkg->config('OPEN_COURSES_TEACHER_GROUP_ID')));
                if ($userIsTeacher) {
                    return true;
                }
                break;
        }
    }
    
    public function sanitize_string($string){
        return filter_var($string, FILTER_SANITIZE_STRING);
    }
    /*
     * Gets the theme object by configured value in configuration of open courses package
     */
    public function get_configured_page_theme() {
        $pkg = Package::getByHandle('open_courses');
        $theme_handle = 'open_courses';
        if ($pkg->config('OPEN_COURSES_SETTINGS_THEME') !== NULL) {
            $theme_handle = $pkg->config('OPEN_COURSES_SETTINGS_THEME');
        }
        $theme = PageTheme::getByHandle($theme_handle);
        if ($theme === NULL) {
            throw new Exception(t("Theme not found. Please check settings for open courses in the dashboard"));
        }
        return $theme;
    }

}
