<?php
defined('C5_EXECUTE') or die("Access Denied.");
// 2DO: how can we use this vars accross include files? (opmitize code)
$u = new User();
$isLoggedIn = $u->isLoggedIn();
if ($isLoggedIn) {
    $ui = UserInfo::getByID($u->getUserID());
}
?>         

<!-- sidebar: style can be found in sidebar.less -->
<section class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel">
        <?php if($isLoggedIn): ?>
        <div class="pull-left image">
            <?php
            // 2DO: add security note to docs, avatars are publicly accessable
            if ($ui->hasAvatar() == true) {
                $av = '<img class="img-circle" src="' . BASE_URL . DIR_REL . '/files/avatars/' . $ui->getUserID() . '.jpg' . '" alt="avatar" />';
                echo $av;
            } else {
                // 2DO: add placeholder
            }
            ?>
        </div>
        <div class="pull-left info">
            <p>Hello, <?php echo $u->getUserName(); ?></p>

            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
        
        <?php endif; ?>
        
    </div>
    <!-- search form -->
    <!--<form action="#" method="get" class="sidebar-form">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Search..."/>
            <span class="input-group-btn">
                <button type='submit' name='seach' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
            </span>
        </div>
    </form>-->
    <!-- /.search form -->
    <!-- sidebar menu: : style can be found in sidebar.less -->

    <?php
    $a = new GlobalArea('Open-Courses-Global-Sidebar');
    $a->display($c);
    ?>

    <?php /*<ul class="sidebar-menu">
        <li class="active">
            <a href="<?php echo $this->url('/'); ?>">
                <i class="fa fa-dashboard"></i> <span><?php echo t('Dashboard'); ?></span>
            </a>
        </li>
    </ul> */ ?>
</section>
<!-- /.sidebar -->
