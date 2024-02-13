bs.util.registerNamespace( 'bs.reminder.ui.workflows.inspector' );

bs.reminder.ui.workflows.inspector.SetReminderInspector = function( element, dialog ) {
	bs.reminder.ui.workflows.inspector.SetReminderInspector.parent.call( this, element, dialog );
};

OO.inheritClass( bs.reminder.ui.workflows.inspector.SetReminderInspector, workflows.editor.inspector.ActivityInspector );

bs.reminder.ui.workflows.inspector.SetReminderInspector.prototype.getDialogTitle = function() {
	return mw.message( 'bs-reminder-ui-workflows-inspector-activity-set-reminder-title' ).text();
};

bs.reminder.ui.workflows.inspector.SetReminderInspector.prototype.getItems = function() {
	return  [
		{
			type: 'section_label',
			title: mw.message( 'workflows-ui-editor-inspector-properties' ).text()
		},
		{
			type: 'user_picker',
			name: 'properties.username',
			label: mw.message( 'bs-reminder-ui-workflows-inspector-activity-set-reminder-property-username' ).text(),
			required: true
		},
		{
			type: 'title',
			name: 'properties.page',
			label: mw.message( 'bs-reminder-ui-workflows-inspector-activity-set-reminder-property-page' ).text(),
			required: true
		},
		{
			type: 'date',
			name: 'properties.date',
			label: mw.message( 'bs-reminder-ui-workflows-inspector-activity-set-reminder-property-date' ).text(),
			widget_$overlay: this.dialog.$overlay,
			required: true
		},
		{
			type: 'text',
			name: 'properties.comment',
			label: mw.message( 'bs-reminder-ui-workflows-inspector-activity-set-reminder-property-comment' ).text()
		}
	];
};

workflows.editor.inspector.Registry.register( 'set_reminder', bs.reminder.ui.workflows.inspector.SetReminderInspector );
