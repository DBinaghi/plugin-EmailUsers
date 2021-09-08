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

<a class="add-page button green" href="<?php echo html_escape(url('email-users/index/send')); ?>"><?php echo __('Send a new e-mail'); ?></a>
<?php if (!has_loop_records('email_users_message')): ?>
    <p><?php echo __('No e-mail message has been sent yet.'); ?> <a href="<?php echo html_escape(url('email-users/index/send')); ?>"><?php echo __('Send a new e-mail'); ?></a>.</p>
<?php else: ?>
	<p><?php echo __('Here are the e-mail messages already sent to users of this site.'); ?></p>
	<table class="full">
		<thead>
			<tr>
				<?php 
					echo browse_sort_links(
						array(
							__('Subject') => 'subject',
							__('Sender') => 'sender',							
							__('Recipients') => 'recipients',							
							__('Type') => 'type',
							__('Priority') => 'priority',
							__('Datetime sent') => 'datetime_sent'
						),
						array('link_tag' => 'th scope="col"', 'list_tag' => '')
					);
				?>
			</tr>
		</thead>
		<tbody>
		<?php foreach (loop('email_users_message') as $emailUsersMessage): ?>
			<tr>
				<td>
					<span class="title">
						<a href="<?php echo html_escape(record_url('email_users_message', 'show')); ?>">
							<?php echo $emailUsersMessage->subject; ?>
						</a>
					</span>
				</td>
				<td>
					<?php 
						$user = get_record_by_id('User', $emailUsersMessage->sender_id);
						echo $user->name;
					?>
				</td>
				<td><?php echo $emailUsersMessage->getRecipientsCount($emailUsersMessage->id); ?></td>
				<td><?php echo ($emailUsersMessage->html == 1 ? __('HTML') : __('Text')); ?></td>
				<td><?php echo ($emailUsersMessage->priority == 1 ? __('High') : __('Normal')); ?></td>
				<td><?php echo html_escape($emailUsersMessage->datetime_sent); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<?php echo foot(); ?>
