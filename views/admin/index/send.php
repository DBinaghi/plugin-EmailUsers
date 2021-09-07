<?php 
	queue_js_file('vendor/tinymce/tinymce.min');
	queue_js_file('email-users-wysiwyg');
	echo head(array('title' => __('E-mail Users | Send a new e-mail'), 'bodyclass' => 'email_users'));
?>

<?php echo flash(); ?>

<form action="<?php echo html_escape(url(array('action' => 'send'))); ?>" method="post" accept-charset="utf-8">
	<section class="seven columns alpha">
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
				<label><?php echo __('E-mail subject')?></label>	
			</div>
			<div class="inputs five columns omega">
				<div class="input-block">		
					<?php
						echo $this->formText('email-users-subject', $_POST['email-users-subject']);		  
					?>
				</div>
			</div>
		</div>
		<div class="field">
			<div class="two columns alpha">
				<label><?php echo __('E-mail text')?></label>	
			</div>
			<div class="inputs five columns omega">
				<div class="input-block">		
					<?php 
						$options = array();
						$options['rows'] = 15;
						$options['cols'] = 50;
						if ((bool)get_option('email_users_composition_use_html')) $options['class'] = array('html-editor');
						echo $this->formTextarea('email-users-text', $_POST['email-users-text'], $options);		  
					?>
				</div>
			</div>
		</div>
	</section>
	<section class="three columns omega">
		<div id="save" class="panel">
			<input type="submit" class="submit big green button" name="submit" id="email-users-send" value="<?php echo __('Send e-mail'); ?>" />
			<div id="public-featured">
				<div class="priority">
					<label for="email-users-email-high-priority"><?php echo __('High priority'); ?></label>
					<?php echo $this->formCheckbox('email-users-email-high-priority', null, array('checked' => false)); ?>
				</div>
			</div>
		</div>
	</section>
</form>

<?php echo foot(); ?>
