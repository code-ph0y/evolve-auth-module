<?php $view->extend('::base.html.php'); ?>

<?php $view['slots']->start('include_css') ?>
    <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('modules/auth/css/signup.css'); ?>" />
<?php $view['slots']->stop(); ?>

<section class="content clearfix well">
    <div class="left">
        <h3>Activate Account</h3>
        <div class="text">
            <h4>Thank you for registering at <?php echo $registeringAt; ?></h4>
            <p>Your account has successfully been activated. Please use the form on your right to log in.</p>
            <p><a href="<?php echo $view['router']->generate('AuthModule_Login'); ?>" class="btn btn-success">Proceed to Login</a></p>
        </div>
    </div>
</section>
