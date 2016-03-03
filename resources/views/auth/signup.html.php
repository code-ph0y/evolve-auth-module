<?php $view->extend('::base.html.php'); ?>

<?php $view['slots']->start('include_css') ?>
    <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('modules/auth/css/signup.css') ?>" />
<?php $view['slots']->stop(); ?>

<?php $view['slots']->start('include_js_body') ?>
    <script src="<?php echo $view['assets']->getUrl('modules/auth/js/jquery.validationEngine-en.js') ?>"></script>
    <script src="<?php echo $view['assets']->getUrl('modules/auth/js/jquery.validationEngine.js') ?>"></script>
    <script src="<?php echo $view['assets']->getUrl('modules/auth/js/signup.js') ?>"></script>
<?php $view['slots']->stop(); ?>

<section class="sign-up-panel clearfix">

    <?php if (isset($errors) && !empty($errors)) : ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error) : ?>
                <p><?php echo $view->escape($error);?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $view['router']->generate('AuthModule_Signup_Save');?>" method="post" class="form-horizontal">

        <div>
            <h1>User Sign Up</h1>
            <small>
                <i class="icon-double-angle-right"></i>
                Sign Up For New Account
            </small>
        </div><!-- /.page-header -->
        <hr/>
        <div class="control-group">
            <label class="control-label" for="formFirstName">First Name <em>*</em></label>
            <div class="controls">
                <input type="text" class="input-xlarge validate[required]" id="formFirstName" name="userFirstName" value="<?php echo $user->getFirstName(); ?>" />
                <span rel="formFirstName" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="formLastName">Last Name <em>*</em></label>
            <div class="controls">
                <input type="text" class="input-xlarge validate[required]" id="formLastName" name="userLastName" value="<?php echo $user->getLastName(); ?>" />
                <span rel="formLastName" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="formEmail">Email Address <em>*</em></label>
            <div class="controls">
                <input type="text" class="input-xlarge validate[required,custom[email]]" id="formEmail" name="userEmail" value="<?php echo $user->getEmail(); ?>" />
                <span rel="formEmail" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="formPassword">Password <em>*</em></label>
            <div class="controls">
                <input type="password" class="input-xlarge validate[required]" id="formPassword" name="userPassword" />
                <span rel="formPassword" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="formConfirmPassword">Comfirm Password <em>*</em></label>
            <div class="controls">
                <input type="password" class="input-xlarge validate[required,equals[userPassword]]" id="formConfirmPassword" name="userConfirmPassword" />
                <span rel="formConfirmPassword" class="help-inline"></span>
            </div>
        </div>

        <div class="clearfix form-actions">
            <div>
                <button class="btn btn-info" type="submit"><i class="icon-ok bigger-110"></i>Submit</button>
                &nbsp; &nbsp; &nbsp;
                <button class="btn btn-default" type="reset"><i class="icon-undo bigger-110"></i>Reset</button>
            </div>
        </div>
    </form>

</section>
