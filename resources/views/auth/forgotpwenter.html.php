<?php $view->extend('::base.html.php'); ?>

<?php $view['slots']->start('include_css') ?>
    <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('modules/auth/css/forgotpwenter.css') ?>" />
    <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('modules/auth/css/validationEngine.jquery.css') ?>" />
<?php $view['slots']->stop() ?>

<div id="forgotpw-enter-panel">
    <h3>Change Password</h3>
    <p>Enter in your new password</p>
    <form id="user-pass" action="<?php echo $view['router']->generate('AuthModule_Forgot_Password_Save');?>" method="post">
        <div class="form-group">
	    	<label for="userPassword">Password <em>*</em></label>
	    	<input name="userPassword" type="password" class="validate[required] form-control" id="userPassword" placeholder="Password">
	    </div>

        <div class="form-group">
	    	<label for="userConfirmPassword">Password <em>*</em></label>
	    	<input name="userConfirmPassword" type="password" class="validate[required,equals[password]] form-control" id="userConfirmPassword" placeholder="Confirm Password">
	    </div>

	    <button type="submit" class="btn btn-primary">Save Password</button>

        <input type="hidden" name="csrf" value="<?php echo $csrf ?>">
    </form>
</div>
