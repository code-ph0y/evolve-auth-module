<?php $view->extend('::base.html.php'); ?>

<?php $view['slots']->start('include_css'); ?>
	<link rel="stylesheet" href="<?php echo $view['assets']->getUrl('modules/auth/css/forgotpw.css'); ?>" />
<?php $view['slots']->stop(); ?>

<div id="forgotpw-panel">
	<h4>Forgot your password?</h4>
	<p>
		Fill in your e-mail address and we'll send you a new one.
	</p>
	<form id="user-forgot-password" action="<?php echo $view['router']->generate('AuthModule_Forgot_Password_Send');?>" method="post">
		<div class="form-group">
	    	<label for="userEmail">Email address</label>
	    	<input name="userEmail" type="email" class="validate[required,custom[email]] form-control" id="userEmail" placeholder="Email">
	    </div>
	    <button type="submit" class="btn btn-primary">Request password</button>
	</form>
</div>
