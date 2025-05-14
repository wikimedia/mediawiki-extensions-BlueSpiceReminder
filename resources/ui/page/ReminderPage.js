bs.reminder.ui.ReminderPage = function ( name, cfg ) {
	cfg = cfg || {};
	name = name || 'create-reminder';
	this.canCreateForOthers = cfg.canCreateForOthers || false;
	this.skipActionDefinitions = cfg.skipActionDefinitions || false;

	bs.reminder.ui.ReminderPage.parent.call( this, name, cfg );
	bs.reminder.ui.mixin.RepeatLayout.call( this );

	this.type = 'page';
};

OO.inheritClass( bs.reminder.ui.ReminderPage, OOJSPlus.ui.booklet.DialogBookletPage );
OO.mixinClass( bs.reminder.ui.ReminderPage, bs.reminder.ui.mixin.RepeatLayout );

bs.reminder.ui.ReminderPage.prototype.init = function () {
	bs.reminder.ui.ReminderPage.parent.prototype.init.call( this );
	if ( !this.canCreateForOthers ) {
		this.userSelectorLayout.$element.hide();
	}
};

bs.reminder.ui.ReminderPage.prototype.getItems = function () {
	const today = new Date();
	const currentDate = today.getFullYear() + '-' + ( today.getMonth() + 1 ) + '-' + today.getDate();
	this.datePicker = new mw.widgets.DateInputWidget( {
		$overlay: this.dialog.$overlay,
		required: true,
		mustBeAfter: currentDate
	} );
	this.datePicker.$element.css( 'width', '250px' );

	this.pagePicker = new OOJSPlus.ui.widget.TitleInputWidget( {
		required: true, mustExist: true, $overlay: this.dialog.$overlay
	} );
	this.comment = new OO.ui.MultilineTextInputWidget();

	this.repeatSwitch = new OO.ui.CheckboxInputWidget();
	this.repeatSwitch.connect( this, {
		change: function ( selected ) {
			if ( selected ) {
				this.repeatLayout.$element.show();
			} else {
				this.repeatLayout.$element.hide();
				this.repeatLayout.$element.hide();
			}
			this.updateDialogSize();
		}
	} );
	this.repeatLayout = this.getRepeatLayout();
	this.repeatLayout.$element.hide();

	this.userSelector = new OOJSPlus.ui.widget.UserPickerWidget( {
		$overlay: this.dialog.$overlay,
		id: 'bs-reminder-field-user',
		required: true
	} );

	this.userSelectorLayout = new OO.ui.FieldLayout( this.userSelector, {
		label: mw.message( 'bs-reminder-user-label' ).text(),
		align: 'top'
	} );

	return [
		new OO.ui.FieldLayout( this.pagePicker, {
			label: mw.message( 'bs-reminder-article-label' ).plain(),
			align: 'top'
		} ),
		this.userSelectorLayout,
		new OO.ui.HorizontalLayout( {
			items: [
				new OO.ui.FieldLayout( this.datePicker, {
					label: mw.message( 'bs-reminder-date-label' ).plain(),
					align: 'top'
				} ),
				new OO.ui.FieldLayout( this.repeatSwitch, {
					label: mw.message( 'bs-reminder-repeat-label' ).plain(),
					align: 'top'
				} )
			]
		} ),
		this.repeatLayout,
		new OO.ui.FieldLayout( this.comment, {
			label: mw.message( 'bs-reminder-comment-label' ).plain(),
			align: 'top'
		} )
	];
};

bs.reminder.ui.ReminderPage.prototype.getTitle = function () {
	return mw.message( 'bs-reminder-create-title' ).plain();
};

bs.reminder.ui.ReminderPage.prototype.getSize = function () {
	return 'medium';
};

bs.reminder.ui.ReminderPage.prototype.setData = function ( data ) {
	data = data || {};
	if ( data.hasOwnProperty( 'user' ) ) {
		this.userSelector.setValue( data.user );
	}
	if ( data.hasOwnProperty( 'page' ) ) {
		this.pagePicker.setValue( data.page );
	}
	if ( data.hasOwnProperty( 'type' ) ) {
		this.type = data.type;
	}
	if ( data.hasOwnProperty( 'date' ) ) {
		this.datePicker.setValue( this.formatDateForInput( this.dateFromValue( data.date ) ) );
	}
	if ( data.hasOwnProperty( 'comment' ) ) {
		this.comment.setValue( data.comment );
	}
	if ( data.hasOwnProperty( 'isRepeating' ) ) {
		this.repeatSwitch.setSelected( data.isRepeating );
	}
	if ( data.hasOwnProperty( 'id' ) ) {
		this.reminderId = data.id;
	}

	this.setRepeatData( data );

	this.updateDialogSize();
};

bs.reminder.ui.ReminderPage.prototype.getActionKeys = function () {
	const actions = [ 'cancel', 'done', 'page-reminders' ];
	if ( this.canCreateForOthers ) {
		actions.push( 'manage-all' );
	}
	if ( !mw.user.isAnon() ) {
		actions.push( 'my-reminders' );
	}

	return actions;
};

bs.reminder.ui.ReminderPage.prototype.getAbilities = function () {
	const abilities = { cancel: true, done: true, 'my-reminders': true, 'page-reminders': true };
	if ( this.canCreateForOthers ) {
		abilities[ 'manage-all' ] = true;
	}
	return abilities;
};

bs.reminder.ui.ReminderPage.prototype.getActionDefinitions = function () {
	if ( this.skipActionDefinitions ) {
		return {};
	}

	const defs = {
		'page-reminders': {
			action: 'page-reminders', label: mw.message( 'bs-reminder-dialog-page-action-page-reminders' ).text()
		}
	};
	if ( this.canCreateForOthers ) {
		defs[ 'manage-all' ] = {
			action: 'manage-all', label: mw.message( 'bs-reminder-dialog-page-action-all-reminders' ).text()
		};
	}
	if ( !mw.user.isAnon() ) {
		defs[ 'my-reminders' ] = {
			action: 'my-reminders', label: mw.message( 'bs-reminder-dialog-page-action-my-reminders' ).text()
		};
	}

	return defs;
};

bs.reminder.ui.ReminderPage.prototype.onAction = function ( action ) {
	const dfd = $.Deferred();

	if ( action === 'done' ) {
		this.checkValidity( [
			this.userSelector,
			this.pagePicker,
			this.datePicker
		] ).done( () => {
			this.createReminder().done( () => {
				dfd.resolve( { action: 'close', data: { success: true } } );
			} ).fail( ( error ) => {
				dfd.reject( error );
			} );
		} ).fail( () => {
			// Do nothing
			dfd.resolve( {} );
		} );
	} else if ( action === 'page-reminders' ) {
		dfd.resolve( {} );
		window.location.href = mw.util.getUrl( 'Special:Reminder', { page: mw.config.get( 'wgPageName' ) } );
	} else if ( action === 'manage-all' ) {
		dfd.resolve( {} );
		window.location.href = mw.util.getUrl( 'Special:Reminder' );
	} else if ( action === 'my-reminders' ) {
		dfd.resolve( {} );
		window.location.href = mw.util.getUrl( 'Special:Reminder', { user: mw.config.get( 'wgUserName' ) } );
	} else {
		return bs.reminder.ui.ReminderPage.parent.prototype.onAction.call( this, action );
	}

	return dfd.promise();
};

bs.reminder.ui.ReminderPage.prototype.createReminder = function () {
	const dfd = $.Deferred();

	blueSpice.api.tasks.exec(
		'reminder',
		'saveReminder',
		this.getValue(), {
			success: function () {
				dfd.resolve();
			},
			failure: function ( response ) {
				dfd.reject( response.message );
			}
		}
	);

	return dfd.promise();
};

bs.reminder.ui.ReminderPage.prototype.formatDateForInput = function ( date ) {
	if ( !date ) {
		return '';
	}

	const year = date.getFullYear();
	const month = String( date.getMonth() + 1 ).padStart( 2, '0' );
	const day = String( date.getDate() ).padStart( 2, '0' );

	return `${ year }-${ month }-${ day }`;
};

bs.reminder.ui.ReminderPage.prototype.dateFromValue = function ( value ) {
	return new Date( Date.parse( value ) );
};

bs.reminder.ui.ReminderPage.prototype.getValue = function () {
	return {
		page: this.pagePicker.getValue(),
		userName: this.userSelector.getValue(),
		comment: this.comment.getValue(),
		id: this.reminderId || 0,
		date: this.datePicker.getValue(),
		isRepeating: this.repeatSwitch.isSelected(),
		repeatDateEnd: this.repeatEnd.getValue(),
		repeatConfig: this.getRepeatValue(),
		type: this.type
	};
};
