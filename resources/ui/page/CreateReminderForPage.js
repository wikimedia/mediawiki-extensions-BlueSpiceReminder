bs.reminder.ui.CreateReminderForPage = function( cfg ) {
	bs.reminder.ui.CreateReminderForPage.parent.call( this, 'create-reminder', cfg );
};

OO.inheritClass( bs.reminder.ui.CreateReminderForPage, bs.reminder.ui.ReminderPage );

bs.reminder.ui.CreateReminderForPage.prototype.init = function() {
	bs.reminder.ui.CreateReminderForPage.parent.prototype.init.call( this );
	this.pagePicker.$element.parents( '.oo-ui-fieldLayout' ).hide();
};
