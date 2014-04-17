<?php

/*
 * only here because we need to extend tinymce to allow video/audio html5 tags
 * thanks to http://www.concrete5.org/documentation/how-tos/developers/extend-the-rich-text-editor/
 */
defined('C5_EXECUTE') or die("Access Denied.");

class OpenCoursesContentBlockController extends ContentBlockController {

    public function getBlockTypeDescription() {
        return t("HTML/WYSIWYG Editor Content for Open Courses.");
    }

    public function getBlockTypeName() {
        return t("OpenEoto Courses Content");
    }

}
