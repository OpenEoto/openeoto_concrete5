<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('HTTP/1.1 401 Unauthorized');
}
?>
<h2><?php echo t('Sorry, although we really like Open Access, you have no permission for this course. Please contact the administrator if you have further questions or problems!'); ?></h2>

