<?php
defined('C5_EXECUTE') or die("Access Denied.");
$home = DIR_REL;
// check if toolbar is visible, only works for 5.6.x
global $cp;
$css_class = "";
if ($cp->canViewToolbar()) {
    $css_class = "open-courses-toolbar-visible";
}
$u = new User();
$isLoggedIn = $u->isLoggedIn();
if($isLoggedIn){
    $ui = UserInfo::getByID($u->getUserID());
}
?>
<html>
    <head>
        <?php Loader::element('header_required'); ?>

        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- bootstrap 3.0.2 -->
        <link href="<?php echo $this->getThemePath() ?>/adminlte/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- font Awesome -->
        <link href="<?php echo $this->getThemePath() ?>/adminlte/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="<?php echo $this->getThemePath() ?>/adminlte/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="<?php echo $this->getThemePath() ?>/adminlte/css/AdminLTE.css" rel="stylesheet" type="text/css" />

        <!-- Custom additions: -->
        <link href="<?php echo $this->getThemePath() ?>/style.css" rel="stylesheet" type="text/css" />
        
        <link href='http://fonts.googleapis.com/css?family=Arvo' rel='stylesheet' type='text/css'>

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>

    <body class="skin-black <?php echo $css_class; ?>">
        <!-- header logo: style can be found in header.less -->
        <header class="header">
            <a href="<?php echo $home; ?>" class="logo">
                <!-- Add the class icon to your logo image or logo icon to add the margining -->
                <?php //2DO: use site logo or site name and make sure long names won't break the design ?>
                <?php echo SITE; ?>
                </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="navbar-right">


                    <?php if ($isLoggedIn): ?>
                        <ul class="nav navbar-nav">
                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="glyphicon glyphicon-user"></i>
                                    <span><?php echo $u->getUserName(); ?> <i class="caret"></i></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header bg-light-blue">
                                        <?php
                                        // 2DO: add security note to docs, avatars are publicly accessable
                                        if ($ui->hasAvatar() == true) {
                                            $av = '<img class="img-circle" src="' . BASE_URL . DIR_REL . '/files/avatars/' . $ui->getUserID() . '.jpg' . '" alt="avatar" />';
                                            echo $av;
                                        } else {
                                            // 2DO: add placeholder
                                        }
                                        ?>
                                        <p>
                                            <?php echo $u->getUserName(); ?>
                                            <!-- <small>Member since Nov. 2012</small> -->
                                        </p>
                                    </li>
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="<?php echo $this->url('/profile'); ?>" class="btn btn-default btn-flat"><?php echo t('Profile'); ?></a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="<?php echo $this->url('/login', 'logout'); ?>" class="btn btn-default btn-flat"><?php echo t('Sign out'); ?></a>
                                        </div>
                                    </li>
                                </ul>
                            </li>

                        </ul>
                    <?php else:  // not logged in ?>
                        <ul class="nav navbar-nav"><li>
                                <a href="<?php echo $this->url('/login'); ?>" class="btn btn-flat"><?php echo t('Sign in'); ?></a>
                            </li></ul>



                    <?php endif; // eo logged in ?>


                </div>
            </nav>
        </header>

        <div class="wrapper row-offcanvas row-offcanvas-left">