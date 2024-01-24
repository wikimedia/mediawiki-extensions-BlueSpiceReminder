workflows.editor.element.registry.register( 'set_reminder', {
	isUserActivity: false,
	class: 'activity-set-reminder activity-bootstrap-icon',
	label: mw.message( 'bs-reminder-ui-workflows-inspector-activity-set-reminder-title' ).text(),
	defaultData: {
		properties: {
			username: '',
			page: '',
			date: '',
			comment: ''
		}
	}
} );
