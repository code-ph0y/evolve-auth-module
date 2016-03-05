<?php $view->extend('::base.html.php'); ?>

<?php $view['slots']->start('include_css') ?>
    <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('modules/auth/css/login.css'); ?>"/>
<?php $view['slots']->stop(); ?>

<div id="login-panel">
    <div>
        <h1>User Login</h1>
        <small>
            <i class="icon-double-angle-right"></i>
            Log Into Your Account
        </small>
    </div><!-- /.page-header -->
    <hr />
    <?php if (isset($errors) && !empty($errors)) : ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error) : ?>
                <p><?php echo $view->escape($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form
        action="<?php echo $view['router']->generate('AuthModule_Login_Check'); ?>"
        method="post"
        class="form-horizontal"
        role="form"
        id="validation-form"
    >

        <div class="form-group">
            <label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Email Address </label>
            <div class="col-sm-9">
                <input type="text" id="form-field-1" placeholder="Email Address" class="col-xs-10 col-sm-5" name="userEmail">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label no-padding-right" for="form-field-2"> Password </label>
            <div class="col-sm-9">
                <input type="password" id="form-field-2" placeholder="Password" class="col-xs-10 col-sm-5" name="userPassword">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-12">
                <a href="<?php echo $view['router']->generate('AuthModule_Forgot_Password'); ?>">Forgot Password?</a>
            </div>
        </div>

        <div class="clearfix form-actions">
            <div class="col-md-offset-3 col-md-9">
                <button class="btn btn-info" type="submit"><i class="icon-ok bigger-110"></i>Submit</button>
                &nbsp; &nbsp; &nbsp;
                <button class="btn" type="reset"><i class="icon-undo bigger-110"></i>Reset</button>
            </div>
        </div>
    </form>
</div>
<?php $view['slots']->start('include_js_body'); ?>
    <script src="<?php echo $view['assets']->getUrl('modules/auth/js/jquery.validationEngine.js'); ?>"></script>
    <script src="<?php echo $view['assets']->getUrl('modules/auth/js/login.js'); ?>"></script>
<?php $view['slots']->stop(); ?>
