Ext.define( 'BS.Reminder.flyout.Base', {
	extend: 'BS.flyout.TwoColumnsBase',
	requires: [
		'BS.Reminder.flyout.form.Reminder',
		'BS.Reminder.flyout.grid.ReminderPanel',
		'BS.Reminder.flyout.dataview.Upcoming'
	],
	makeCenterTwoItems: function() {
		this.reminderGrid = new BS.Reminder.flyout.grid.ReminderPanel( {} );

		return [
			this.reminderGrid
		]
	},

	makeCenterOneItems: function() {
		this.reminderForm = new BS.Reminder.flyout.form.Reminder();
		this.reminderForm.on( 'save', this.saveReminder, this );

		return [
			this.reminderForm
		]
	},

	makeTopPanelItems: function() {
		this.upcomingReminders = new BS.Reminder.flyout.dataview.Upcoming();
		return [
			this.upcomingReminders
		]
	},

	makeBottomPanelItems: function() {
		this.btnManager = new Ext.Button( {
			text: mw.message( 'bs-reminder-flyout-manager-btn-label' ).plain()
		} );
		this.btnMyReminder = new Ext.Button( {
			text: mw.message( 'bs-reminder-flyout-manager-my-reminder-btn-label' ).plain(),
			margin: '0 0 0 10px'
		} );
		this.btnManager.on( 'click', this.onBtnManagerClick );
		this.btnMyReminder.on( 'click', this.onBtnMyReminder );
		return [
			this.btnManager,
			this.btnMyReminder
		];
	},

	onBtnManagerClick: function() {
		var url = mw.util.getUrl( "Special:Reminder/" );
		window.location = url;
	},

	onBtnMyReminder: function() {
		var user = mw.user.getName();
		var url = mw.util.getUrl( "Special:Reminder/" + user );
		window.location = url;
	},

	saveReminder: function( form, data ) {
		var me = this;
		bs.api.tasks.exec(
			'reminder',
			'saveReminder',
			data
		).done( function( response ) {
			form.reset();
			form.setButtonsDisabled( true );
			me.reminderGrid.store.reload();
			me.upcomingReminders.store.reload();
		} );
	}
} );
