bs.reminder.ui.ReminderPage = function( name, cfg ) {
	this.canCreateForOthers = cfg.canCreateForOthers || false;

	bs.reminder.ui.ReminderPage.parent.call( this, name, cfg );
	bs.reminder.ui.mixin.RepeatLayout.call( this );

	if ( !this.canCreateForOthers ) {
		this.userSelectorLayout.$element.hide();
	}

	if ( cfg.hasOwnProperty( 'data' ) ) {
		this.setValue( cfg.data );
	}
};

OO.inheritClass( bs.reminder.ui.ReminderPage, OOJSPlus.ui.booklet.DialogBookletPage );
OO.mixinClass( bs.reminder.ui.ReminderPage, bs.reminder.ui.mixin.RepeatLayout );

bs.reminder.ui.ReminderPage.prototype.getItems = function() {
	this.datePicker = new mw.widgets.DateInputWidget( {
		$overlay: true,
		required: true
	} );
	this.datePicker.$element.css( 'width', '250px' );

	this.pagePicker = new mw.widgets.TitleInputWidget( { required: true } );
	this.comment = new OO.ui.MultilineTextInputWidget();

	this.repeatSwitch = new OO.ui.CheckboxInputWidget();
	this.repeatSwitch.connect( this, {
		change: function( selected ) {
			if ( selected ) {
				this.repeatLayout.$element.show();
			} else {
				this.repeatLayout.$element.hide();
			}
			this.updateDialogSize();
		}
	} );
	this.repeatLayout = this.getRepeatLayout();
	this.repeatLayout.$element.hide();

	this.userSelector = new mw.widgets.UserInputWidget( {
		$overlay: true,
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

bs.reminder.ui.ReminderPage.prototype.getTitle = function() {
	return mw.message( 'bs-reminder-create-title' ).plain();
};

bs.reminder.ui.ReminderPage.prototype.getTitle = function() {
	return mw.message( 'bs-reminder-create-title' ).plain();
};

bs.reminder.ui.ReminderPage.prototype.getSize = function() {
	return 'medium';
};

bs.reminder.ui.ReminderPage.prototype.setValue = function( value ) {
	this.userSelector.setValue( value.user || '' );
	this.pagePicker.setValue( value.page || '' );
	this.datePicker.setValue( value.hasOwnProperty( 'date' ) ? this.formatDateForInput( this.dateFromValue( value.date ) ) : '' );
	this.comment.setValue( value.comment || '' );
	this.repeatSwitch.setSelected( value.isRepeating || false );
	this.setRepeatValue( value );
	this.reminderId = value.id || 0;

	this.updateDialogSize();
};

bs.reminder.ui.ReminderPage.prototype.getActionKeys = function() {
	var actions = [ 'cancel', 'done', 'page-reminders' ];
	if ( this.canCreateForOthers ) {
		actions.push( 'manage-all' );
	}
	if ( !mw.user.isAnon() ) {
		actions.push( 'my-reminders' );
	}

	return actions;
};

bs.reminder.ui.ReminderPage.prototype.getAbilities = function() {
	var abilities = { cancel: true, done: true, 'my-reminders': true, 'page-reminders': true };
	if ( this.canCreateForOthers ) {
		abilities['manage-all'] = true;
	}
	return abilities;
};

bs.reminder.ui.ReminderPage.prototype.getActionDefinitions = function() {
	var defs = {
		'page-reminders': {
			action: 'page-reminders', label: 'Reminders for this page',
			href: mw.util.getUrl( 'Special:Reminder', { page: mw.config.get( 'wgPageName' ) } )
		}
	};
	if ( this.canCreateForOthers ) {
		defs['manage-all'] = {
			action: 'manage-all', label: 'Manage all reminders',
			href: mw.util.getUrl( 'Special:Reminder' )
		};
	}
	if ( !mw.user.isAnon() ) {
		defs['my-reminders'] = {
			action: 'my-reminders', label: 'My reminders',
			href: mw.util.getUrl( 'Special:Reminder', { user: mw.user.getName() } )
		};
	}

	return defs;
};

bs.reminder.ui.ReminderPage.prototype.onAction = function( action ) {
	var dfd = $.Deferred();

	if ( action === 'done' ) {
		this.checkValidity().done( function() {
			this.createReminder().done( function() {
				dfd.resolve( { action: 'close', data: { success: true } } );
			}.bind( this ) ).fail( function( error ) {
				dfd.reject( error );
			} );
		}.bind( this ) ).fail( function() {
			// Do nothing
			dfd.resolve( {} );
		} );
	} else {
		return bs.reminder.ui.ReminderPage.parent.prototype.onAction.call( this, action );
	}

	return dfd.promise();
};

bs.reminder.ui.ReminderPage.prototype.checkValidity = function() {
	var dfd = $.Deferred();

	var inputs = [
		this.userSelector,
		this.pagePicker,
		this.datePicker
	];
	this.doCheckValidity( inputs, dfd );

	return dfd.promise();
};

bs.reminder.ui.ReminderPage.prototype.doCheckValidity = function( inputs, dfd ) {
	if ( inputs.length === 0 ) {
		dfd.resolve();
		return;
	}
	var input = inputs.shift();

	input.getValidity().done( function() {
		input.setValidityFlag( true );
		this.doCheckValidity( inputs, dfd );
	}.bind( this ) ).fail( function() {
		input.setValidityFlag( false );
		dfd.reject();
	} );
};

bs.reminder.ui.ReminderPage.prototype.createReminder = function() {
	var dfd = $.Deferred();

	blueSpice.api.tasks.exec(
		'reminder',
		'saveReminder',
		this.getValue(), {
			success: function() {
				dfd.resolve();
			},
			failure: function( response ) {
				dfd.reject( response.message );
			}
		}
	);

	return dfd.promise();
};


bs.reminder.ui.ReminderPage.prototype.formatDateForInput = function( date ) {
	if ( !date ) {
		return '';
	}
	// Awesome JS Date handling
	return date.toISOString().split('T')[0];
};

bs.reminder.ui.ReminderPage.prototype.dateFromValue = function( value ) {
	return new Date( Date.parse( value ) );
};

bs.reminder.ui.ReminderPage.prototype.getValue = function() {
	return {
		page: this.pagePicker.getValue(),
		userName: this.userSelector.getValue(),
		comment: this.comment.getValue(),
		id: this.reminderId || 0,
		date: this.datePicker.getValue(),
		isRepeating: this.repeatSwitch.isSelected(),
		repeatDateEnd: this.repeatEnd.getValue(),
		repeatConfig: this.getRepeatValue()
	};
};
