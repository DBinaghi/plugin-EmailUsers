<?php 
	queue_js_file('vendor/tinymce/tinymce.min');
	queue_js_file('email-users-wysiwyg');
	if ($email_users_message->id != '') {
		echo head(array('title' => __('E-mail Users | Edit e-mail #%s', $email_users_message->id), 'bodyclass' => 'email_users'));
	} else {
		echo head(array('title' => __('E-mail Users | Compose new e-mail'), 'bodyclass' => 'email_users'));
	}
?>

<?php echo flash(); ?>

<form action="<?php echo html_escape(url(array('action' => 'edit'))); ?>" method="post" accept-charset="utf-8">
	<?php echo '<input id="email-users-id" name="email-users-id" type="hidden" value="' . $email_users_message->id . '">'; ?>
	<section class="seven columns alpha">
		<p>
		<?php 
			if ($email_users_message->id != '') {
				echo __('In this page you can edit details of an existing e-mail'); 
			} else {
				echo __('In this page you can compose a new e-mail'); 
			}
			echo __('; please remember that both <b>Subject</b> and <b>Text</b> fields are mandatory, and that <b>Super</b> and <b>Admin</b> roles will receive by default a copy of the e-mail. If you so wish, you can always save the details as a draft and edit/send them at a later time.');
		?>
		</p>
		<hr>
		<div class="field">
			<div class="two columns alpha">
				<label><?php echo __('Recipients')?></label>	
			</div>
			<div class="inputs five columns omega">
				<div class="input-block">		
					<?php 
					$userRoles = get_user_roles();
					
					echo '<ul style="list-style-type:none; padding-left: 0; margin-top: 0; margin-bottom: 0">';
					foreach($userRoles as $role => $label) {
						echo '<li>';
						$options = array();
						if ($role == 'super' || $role == 'admin') {
							$options['checked'] = 'checked';
							$options['disabled'] = 'disabled';
						}
						echo $this->formCheckbox('email-users-recipients[]', $role, $options);		  
						echo ' ' . __($label);
						echo '</li>';
					}   
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
		<div class="field">
			<div class="two columns alpha">
				<label><?php echo __('E-mail Subject')?></label>	
			</div>
			<div class="inputs five columns omega">
				<div class="input-block">		
					<?php
						echo $this->formText('email-users-subject', $email_users_message->subject);		  
					?>
				</div>
			</div>
		</div>
		<div class="field">
			<div class="two columns alpha">
				<label><?php echo __('E-mail Text')?></label>	
			</div>
			<div class="inputs five columns omega">
				<div class="input-block">		
					<?php 
						$options = array();
						$options['rows'] = 15;
						$options['cols'] = 50;
						if ((bool)get_option('email_users_composition_use_html')) $options['class'] = array('html-editor');
						echo $this->formTextarea('email-users-text', $email_users_message->text, $options);		  
					?>
				</div>
			</div>
		</div>
	</section>
	<section class="three columns omega">
		<div id="save" class="panel">
			<input type="submit" class="submit big green button" name="email-users-send" id="email-users-send" value="<?php echo __('Send e-mail'); ?>" />
			<input type="submit" class="submit big blue button" name="email-users-save" id="email-users-save" value="<?php echo __('Save as draft'); ?>" />
			<a class="submit big blue button" name="email-users-cancel" id="email-users-save" href="<?php echo html_escape(url('email-users/index/')) . '">' . __('Cancel'); ?></a>
			<div id="public-featured">
				<div class="priority">
					<label for="email-users-priority"><?php echo __('High priority'); ?></label>
					<?php echo $this->formCheckbox('email-users-priority', $email_users_message->priority, null, array('1', '0')); ?>
				</div>
			</div>
		</div>
	</section>
</form>

<?php echo foot(); ?>