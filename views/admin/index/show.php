<?php 
	echo head(array('title' => __('E-mail Users | Show e-mail #%s', $email_users_message->id), 'bodyclass' => 'email_users'));
?>

<a class="browse-email button blue" href="<?php echo html_escape(url('email-users/index/')); ?>"><?php echo __('Browse e-mail'); ?></a>
<?php
	if ($email_users_message->sent == '') {
		echo '<a class="edit-email button blue" href="' . html_escape(url('email-users/index/edit/id/') . $email_users_message->id) . '">' . __('Edit this e-mail') . '</a>';
	}
?>

<p>
	<?php echo __('Here\'s e-mail message #%s.', $email_users_message->id); ?>
	<?php 
		echo '<a href="' . html_escape(url('email-users/index/')) . '">' . __('Browse e-mail') . '</a>';
		if ($email_users_message->sent == '') echo ' ' . __('or') . ' <a href="' . html_escape(url('email-users/index/edit/id/') . $email_users_message->id) . '">' . __('Edit this e-mail') . '</a>';
		echo '.'; 
	?>
</p>
<hr>
<div class="field">
	<div class="two columns alpha">
		<label><?php echo __('Creator')?></label>	
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
		<label><?php echo __('Created on')?></label>	
	</div>
	<div class="inputs eight columns omega">
		<?php echo $email_users_message->created; ?>
	</div>
</div><div class="field">
	<div class="two columns alpha">
		<label><?php echo __('Sent on')?></label>	
	</div>
	<div class="inputs eight columns omega">
		<?php echo ($email_users_message->sent == '' ? '&nbsp;' : $email_users_message->sent); ?>
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
		<?php 
			echo ($email_users_message->sent != '' ? $email_users_message->getRecipientRolesCount($email_users_message->id, get_option('email_users_recipient_count_verbose')) : '&nbsp;'); 
		?>
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
