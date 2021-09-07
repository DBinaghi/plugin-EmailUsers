jQuery(document).ready(function() {
    var selector;
    selector = '#email-users-text';
	if (jQuery(selector).hasClass('html-editor')) {
		Omeka.wysiwyg({
			selector: selector,
			menubar: 'edit view insert format table',
			plugins: 'lists link code paste media autoresize image table charmap hr',
			browser_spellcheck: true
		});
	}
});
