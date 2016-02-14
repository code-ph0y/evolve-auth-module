<table width="600" border="0" align="center" cellpadding="0" cellspacing="0">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Hi <?php echo $toUser->getFullName();?>,</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
			Thank you for registering at <?php echo $registeringAt; ?>.<br>
			<br>
			Please use the following link to activate and complete your registration:<br>
			<?php echo $activationLink;?>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
			Kind Regards,<br>
			<?php echo $teamName; ?>
		</td>
	</tr>
</table>
