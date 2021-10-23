bs.reminder.ui.CreateReminderForPage = function( cfg ) {
	bs.reminder.ui.CreateReminderForPage.parent.call( this, 'create-reminder', cfg );
	this.pagePicker.$element.parents( '.oo-ui-fieldLayout' ).hide();
};

OO.inheritClass( bs.reminder.ui.CreateReminderForPage, bs.reminder.ui.ReminderPage );
