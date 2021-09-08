<?php 
	echo head(array('title' => __('E-mail Users | Show e-mail #%s', $email_users_message->id), 'bodyclass' => 'email_users'));
?>

<a class="browse-email button blue" href="<?php echo html_escape(url('email-users/index/')); ?>"><?php echo __('Browse e-mail'); ?></a>

<p><?php echo __('Here\'s e-mail message #%s.', $email_users_message->id); ?> <a href="<?php echo html_escape(url('email-users/index/')); ?>"><?php echo __('Browse e-mail'); ?></a>.</p>
<hr>
<div class="field">
	<div class="two columns alpha">
		<label><?php echo __('Sent on')?></label>	
	</div>
	<div class="inputs eight columns omega">
		<?php echo $email_users_message->datetime_sent; ?>
	</div>
</div>
<div class="field">
	<div class="two columns alpha">
		<label><?php echo __('Sent by')?></label>	
	</div>
	<div class="inputs eight columns omega">
		<?php 
			$user = get_record_by_id('User', $email_users_message->sender_id);
			echo $user->name;
		?>
	</div>
</div>
<div class="field">
	<div class="two columns alpha">
		<label><?php echo __('Priority')?></label>	
	</div>
	<div class="inputs eight columns omega">
		<?php echo ($email_users_message->priority == 1 ? __('High') : __('Normal')); ?>
	</div>
</div>
<div class="field">
	<div class="two columns alpha">
		<label><?php echo __('Recipients')?></label>	
	</div>
	<div class="inputs eight columns omega">
		<?php echo $email_users_message->getRecipientRolesCount($email_users_message->id); ?>
	</div>
</div>
<div class="field">
	<div class="two columns alpha">
		<label><?php echo __('E-mail Subject')?></label>	
	</div>
	<div class="inputs eight columns omega">
		<?php echo $email_users_message->subject; ?>
	</div>
</div>
<div class="field">
	<div class="two columns alpha">
		<label><?php echo __('E-mail Text')?></label>	
	</div>
	<div class="inputs eight columns omega">
		<?php echo $email_users_message->text; ?>
	</div>
</div>

<?php echo foot(); ?>
