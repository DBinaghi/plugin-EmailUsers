<?php
queue_css_file('email-users');
$head = array('bodyclass' => 'email-users browse',
              'title' => html_escape(__('E-mail Users | Browse')),
              'content_class' => 'horizontal-nav');
if (isset($_GET['view'])) {
	$active = $_GET['view'];
}
echo head($head);
?>
<?php echo flash(); ?>

<a class="add-page button green" href="<?php echo html_escape(url('email-users/index/edit')); ?>"><?php echo __('Compose new e-mail'); ?></a>
<?php if (!has_loop_records('email_users_message')): ?>
    <p><?php echo __('No e-mail message has been created or sent yet.'); ?> <a href="<?php echo html_escape(url('email-users/index/edit')); ?>"><?php echo __('Compose new e-mail'); ?></a>.</p>
<?php else: ?>
	<p><?php echo __('Here are all e-mail messages already created and/or sent to users of this site. If a message has not been sent, click on <b>Edit</b> button to edit and send it. Click on a message <b>Subject</b> to see all details of that message.'); ?></p>
	<table class="full">
		<thead>
			<tr>
				<?php 
					echo browse_sort_links(
						array(
							__('Subject') => 'subject',
							__('Creator') => 'sender',							
							__('Created') => 'created',
							__('Type') => 'type',
							__('Priority') => 'priority',
							__('Recipients') => 'recipients',							
							__('Sent') => 'sent'
						),
						array('link_tag' => 'th scope="col"', 'list_tag' => '')
					);
				?>
			</tr>
		</thead>
		<tbody>
		<?php foreach (loop('email_users_message') as $emailUsersMessage): ?>
			<tr>
				<td class="centred-left">
					<a href="<?php echo html_escape(record_url('email_users_message', 'show')); ?>">
						<?php echo $emailUsersMessage->subject; ?>
					</a>
				</td>
				<td class="centred">
					<?php 
						$user = get_record_by_id('User', $emailUsersMessage->sender_id);
						echo $user->name;
					?>
				</td>
				<td class="centred"><?php echo html_escape($emailUsersMessage->created); ?></td>
				<td class="centred"><?php echo ($emailUsersMessage->html == 1 ? __('HTML') : __('Text')); ?></td>
				<td class="centred"><?php echo ($emailUsersMessage->priority == 1 ? __('High') : __('Normal')); ?></td>
				<td class="centred">
					<?php echo ($emailUsersMessage->sent != '' ? $emailUsersMessage->getRecipientsCount($emailUsersMessage->id) : '&nbsp;'); ?>
				</td>
				<td class="centred">
					<?php 
						if ($emailUsersMessage->sent != '') {
							echo html_escape($emailUsersMessage->sent);
						} else {
							echo '<a class="full-width-mobile button blue" href="' . html_escape(url('email-users/index/edit/id/') . $emailUsersMessage->id) .'">' . __('Edit'). '</a>';
						}
					?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<?php echo foot(); ?>
