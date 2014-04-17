<?php
defined('C5_EXECUTE') or die("Access Denied.");
$navItems = $controller->getNavItems(true); // exclude_nav is ignored, unfortunately there is no better way right now
?>

<ol class="breadcrumb">
    <?php
    for ($i = 0; $i < count($navItems); $i++) {
        $ni = $navItems[$i];
        
        if($i == 0){
            echo '<li><a href="' . $ni->url . '" target="' . $ni->target . '"><i class="fa fa-dashboard"></i>' . $ni->name . '</a></li>';
            continue;
        }
        if ($ni->isCurrent) {
            echo '<li class="active"><a href="#">'.$ni->name.'</a></li>';
        } else {
            echo '<li><a href="' . $ni->url . '" target="' . $ni->target . '">' . $ni->name . '</a></li>';
        }
    }
    ?>
</ol>

