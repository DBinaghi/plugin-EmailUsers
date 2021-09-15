<?php echo js_tag('vendor/tinymce/tinymce.min'); ?>
<script type="text/javascript">
	jQuery(document).ready(function () {
		Omeka.wysiwyg({
			selector: '.html-editor'
		});
	});
</script>
<?php $view = get_view(); ?>

<h2><?php echo __('E-mail composition'); ?></h2>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('email_users_composition_subject_prefix', __('Subject Prefix')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('Text to be placed at the beginning of e-mail subject (optional; e.g.: site name).'); ?>
		</p>
		<?php echo $view->formText('email_users_composition_subject_prefix', get_option('email_users_composition_subject_prefix')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('email_users_composition_header', __('Text Header')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('Text to be placed before the beginning of e-mail text (optional).'); ?>
		</p>
		<?php echo $view->formTextarea('email_users_composition_header', get_option('email_users_composition_header'), array('rows' => '10', 'cols' => '60', 'class' => array('html-editor'))); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('email_users_composition_footer', __('Text Footer')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('Text to be placed after the end of e-mail text (optional).'); ?>
		</p>
		<?php echo $view->formTextarea('email_users_composition_footer', get_option('email_users_composition_footer'), array('rows' => '10', 'cols' => '60', 'class' => array('html-editor'))); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('email_users_composition_use_html', __('Use HTML')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('If checked, HTML code will be allowed for e-mail text.'); ?>
		</p>
		<?php echo $view->formCheckbox('email_users_composition_use_html', get_option('email_users_composition_use_html'), null, array('1', '0')); ?>
	</div>
</div>

<h2><?php echo __('E-mail details'); ?></h2>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('email_users_recipient_count_verbose', __('Recipients Count Verbose')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('If checked, information about recipients in Show page will include more details.'); ?>
		</p>
		<?php echo $view->formCheckbox('email_users_recipient_count_verbose', get_option('email_users_recipient_count_verbose'), null, array('1', '0')); ?>
	</div>
</div>
