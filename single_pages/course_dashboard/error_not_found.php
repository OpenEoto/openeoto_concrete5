<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('HTTP/1.1 401 Unauthorized');
}
?>
<h2><?php echo t('Sorry, course not found in database.'); ?></h2>

