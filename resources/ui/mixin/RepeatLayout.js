bs.reminder.ui.mixin.RepeatLayout = function() {};

OO.initClass( bs.reminder.ui.mixin.RepeatLayout );

bs.reminder.ui.mixin.RepeatLayout.prototype.getRepeatLayout = function() {
	this.repeatValue = new OO.ui.NumberInputWidget( { min: 1, value: 1 } );
	this.repeatValue.$element.css( 'width', '200px' );
	this.repeatInterval = new OO.ui.DropdownInputWidget( {
		options: [
			{ data: 'd', label: mw.message( 'bs-reminder-repeat-interval-day' ).plain() },
			{ data: 'w', label: mw.message( 'bs-reminder-repeat-interval-week' ).plain() },
			{ data: 'm', label: mw.message( 'bs-reminder-repeat-interval-month' ).plain() },
			{ data: 'y', label: mw.message( 'bs-reminder-repeat-interval-year' ).plain() },
		]
	} );
	this.repeatInterval.$element.css( 'width', '200px' );
	this.repeatInterval.connect( this, {
		change: function( value ) {
			this.repeatMonthInterval.$element.hide();
			this.repeatDaysOfWeekLayout.$element.hide();
			if ( value === 'w' ) {
				this.repeatDaysOfWeekLayout.$element.show();
			}
			if ( value === 'm' ) {
				this.repeatMonthInterval.setOptions( [
					this.getDayOfTheWeekIntervalOption( this.dateFromValue( this.datePicker.getValue() ) ),
					this.getDayOfTheMonthIntervalOption( this.dateFromValue( this.datePicker.getValue() ) )
				] );
				this.repeatMonthInterval.$element.show();
			}
			this.repeatEnd.setValue(
				this.formatDateForInput( this.getDataForInterval( this.dateFromValue( this.datePicker.getValue() ), value ) )
			);
			this.updateDialogSize();
		}
	} );

	this.repeatEnd = new mw.widgets.DateInputWidget( {
		$overlay: true
	} );

	this.repeatDaysOfWeek = new OO.ui.ButtonGroupWidget( {
		items: [
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-sunday-abbr' ).plain(),
				data: 0
			} ),
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-monday-abbr' ).plain(),
				data: 1
			} ),
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-tuesday-abbr' ).plain(),
				data: 2
			} ),
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-wednesday-abbr' ).plain(),
				data: 3
			} ),
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-thursday-abbr' ).plain(),
				data: 4
			} ),
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-friday-abbr' ).plain(),
				data: 5
			} ),
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-saturday-abbr' ).plain(),
				data: 6
			} ),
		]
	} );
	this.repeatDaysOfWeekLayout = new OO.ui.FieldLayout( this.repeatDaysOfWeek, {
		label: mw.message( "bs-reminder-repeat-on-title" ).text(),
		align: 'left'
	} );
	this.repeatDaysOfWeekLayout.$element.hide();

	this.repeatMonthInterval = new OO.ui.DropdownInputWidget( {
		options: [],
	} );
	this.repeatMonthInterval.$element.hide();

	return new OO.ui.PanelLayout( {
		expanded: false,
		padded: true,
		text: mw.message( 'bs-reminder-repeat-every-title' ).plain(),
		content: [
			new OO.ui.HorizontalLayout( { items: [ this.repeatValue, this.repeatInterval ] } ),
			this.repeatDaysOfWeekLayout,
			this.repeatMonthInterval,
			new OO.ui.FieldLayout( this.repeatEnd, {
				label: mw.message( 'bs-reminder-date-repeat-ends-on-label' ).plain(),
				align: 'left'
			} )
		]
	} );
};

bs.reminder.ui.mixin.RepeatLayout.prototype.getRepeatValue = function() {
	return {
		intervalType: this.repeatValue.getValue(),
		intervalValue: this.repeatInterval.getValue(),
		repeatDateEnd: this.repeatEnd.getValue(),
		weekdaysToRepeat: this.repeatDaysOfWeek.items.map( function( button ) {
			if ( button.getValue() ) {
				return button.getData();
			}
			return null;
		} ).filter( function( value ) {
			return value !== null;
		} ),
		monthlyRepeatInterval: this.repeatMonthInterval.getValue()
	};

};

bs.reminder.ui.mixin.RepeatLayout.prototype.setRepeatData = function( data ) {
	var repeatConfig = data.repeatConfig || {};
	if ( repeatConfig.hasOwnProperty( 'intervalType' ) ) {
		this.repeatValue.setValue( parseInt( repeatConfig.intervalType ) );
	}
	if ( repeatConfig.hasOwnProperty( 'intervalValue' ) ) {
		this.repeatInterval.setValue( repeatConfig.intervalValue );
	}
	if ( repeatConfig.hasOwnProperty( 'intervalValue' ) ) {
		this.repeatInterval.setValue( repeatConfig.intervalValue );
	}
	var date = repeatConfig.repeatDateEnd || data.date;
	if ( date ) {
		date = this.dateFromValue( date );
		this.repeatEnd.setValue( this.formatDateForInput( this.getDataForInterval( date, repeatConfig.repeatInterval || 'd' ) ) );
	}
	if ( repeatConfig.hasOwnProperty( 'weekdaysToRepeat' ) ) {
		var daysToRepeat = repeatConfig.weekdaysToRepeat || [];
		for ( var i = 0; i < daysToRepeat.length; i++ ) {
			var item = this.repeatDaysOfWeek.findItemFromData( daysToRepeat[i] );
			if ( item ) {
				item.setValue( true );
			}
		}
	}
	if ( repeatConfig.hasOwnProperty( 'monthlyRepeatInterval' ) ) {
		this.repeatMonthInterval.setValue( repeatConfig.monthlyRepeatInterval );
	}
};

bs.reminder.ui.mixin.RepeatLayout.prototype.getDataForInterval = function( date, repeatInterval ) {
	if ( !date ) {
		return '';
	}
	if ( repeatInterval === 'd' ) {
		date.setDate( date.getDate() + 1 );
	}
	if ( repeatInterval === 'w' ) {
		date.setDate( date.getDate() + 7 );
	}
	if ( repeatInterval === 'm' ) {
		date.setMonth( date.getMonth() + 1 );
	}
	if ( repeatInterval === 'y' ) {
		date.setYear( date.getYear() + 1 );
	}

	return date;
};

bs.reminder.ui.mixin.RepeatLayout.prototype.getDayOfTheMonthIntervalOption = function( date ) {
	return {
		data: {
			type: 'dayOfTheMonth'
		},
		label: mw.message( 'bs-reminder-monthly-on-day-prefix' ).plain() + ' ' +
			date.getDate()
	};
};

bs.reminder.ui.mixin.RepeatLayout.prototype.getDayOfTheWeekIntervalOption = function( date ) {
	var currentDayNumeric = date.getDay();
	var currentDayText = date.toLocaleString( mw.config.get( 'wgContentLanguage' ), {
		weekday: 'long'
	} );
	var daysInCurrentMonth = new Date( date.getYear(), date.getMonth(), 0 ).getDate();
	var weekOrderNum = Math.ceil( currentDayNumeric / 7 );
	var weekOrder;

	if ( daysInCurrentMonth - currentDayNumeric < 7 ) {
		weekOrder = mw.message( 'bs-reminder-ordinal-last' ).plain();
		weekOrderNum = -1;
	} else {
		weekOrder = this.mapNumbersToOrdinals( weekOrderNum );
	}
	return {
		data: {
			type: 'dayOfTheWeek',
			weekOrder: weekOrderNum,
			weekdayOrder: date.getDay()
		},
		label: mw.message( 'bs-reminder-monthly-on-the-prefix' ).plain() +
			' ' + weekOrder + ' ' + currentDayText
	};
};

bs.reminder.ui.mixin.RepeatLayout.prototype.mapNumbersToOrdinals = function( number ) {
	switch( number ) {
		case 1:
			return mw.message( 'bs-reminder-ordinal-first' ).plain();
		case 2:
			return mw.message( 'bs-reminder-ordinal-second' ).plain();
		case 3:
			return mw.message( 'bs-reminder-ordinal-third' ).plain();
		case 4:
			return mw.message( 'bs-reminder-ordinal-fourth' ).plain();
	}
};
