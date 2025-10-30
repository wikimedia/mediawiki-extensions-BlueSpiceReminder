bs.reminder.ui.mixin.RepeatLayout = function () {};

OO.initClass( bs.reminder.ui.mixin.RepeatLayout );

bs.reminder.ui.mixin.RepeatLayout.prototype.getRepeatLayout = function () {
	this.repeatValue = new OO.ui.NumberInputWidget( { min: 1, value: 1 } );
	this.repeatValue.$element.css( 'width', '200px' );
	this.repeatInterval = new OO.ui.DropdownInputWidget( {
		options: [
			{ data: 'd', label: mw.message( 'bs-reminder-repeat-interval-day' ).text() },
			{ data: 'w', label: mw.message( 'bs-reminder-repeat-interval-week' ).text() },
			{ data: 'm', label: mw.message( 'bs-reminder-repeat-interval-month' ).text() },
			{ data: 'y', label: mw.message( 'bs-reminder-repeat-interval-year' ).text() }
		]
	} );
	this.repeatInterval.$element.css( 'width', '200px' );
	this.repeatInterval.connect( this, {
		change: function ( value ) {
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
		$overlay: this.dialog ? this.dialog.$overlay : true
	} );

	this.repeatDaysOfWeek = new OO.ui.ButtonGroupWidget( {
		items: [
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-sunday-abbr' ).text(),
				data: 0
			} ),
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-monday-abbr' ).text(),
				data: 1
			} ),
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-tuesday-abbr' ).text(),
				data: 2
			} ),
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-wednesday-abbr' ).text(),
				data: 3
			} ),
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-thursday-abbr' ).text(),
				data: 4
			} ),
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-friday-abbr' ).text(),
				data: 5
			} ),
			new OO.ui.ToggleButtonWidget( {
				label: mw.message( 'bs-reminder-saturday-abbr' ).text(),
				data: 6
			} )
		]
	} );
	this.repeatDaysOfWeekLayout = new OO.ui.FieldLayout( this.repeatDaysOfWeek, {
		label: mw.message( 'bs-reminder-repeat-on-title' ).text(),
		align: 'left'
	} );
	this.repeatDaysOfWeekLayout.$element.hide();

	this.repeatMonthInterval = new OO.ui.DropdownInputWidget( {
		options: []
	} );
	this.repeatMonthInterval.$element.hide();

	return new OO.ui.PanelLayout( {
		expanded: false,
		padded: true,
		text: mw.message( 'bs-reminder-repeat-every-title' ).text(),
		content: [
			new OO.ui.HorizontalLayout( { items: [ this.repeatValue, this.repeatInterval ] } ),
			this.repeatDaysOfWeekLayout,
			this.repeatMonthInterval,
			new OO.ui.FieldLayout( this.repeatEnd, {
				label: mw.message( 'bs-reminder-date-repeat-ends-on-label' ).text(),
				align: 'left'
			} )
		]
	} );
};

bs.reminder.ui.mixin.RepeatLayout.prototype.getRepeatValue = function () {
	return {
		intervalType: this.repeatInterval.getValue(),
		intervalValue: this.repeatValue.getValue(),
		repeatDateEnd: this.repeatEnd.getValue(),
		weekdaysToRepeat: this.repeatDaysOfWeek.items.map( ( button ) => {
			if ( button.getValue() ) {
				return button.getData();
			}
			return null;
		} ).filter( ( value ) => value !== null ),
		monthlyRepeatInterval: this.repeatMonthInterval.getValue()
	};

};

bs.reminder.ui.mixin.RepeatLayout.prototype.setRepeatData = function ( data ) {
	const repeatConfig = data.repeatConfig || {};
	if ( repeatConfig.hasOwnProperty( 'intervalValue' ) ) {
		this.repeatValue.setValue( parseInt( repeatConfig.intervalValue ) );
	}
	if ( repeatConfig.hasOwnProperty( 'intervalType' ) ) {
		this.repeatInterval.setValue( repeatConfig.intervalType );
	}
	let date = repeatConfig.repeatDateEnd || data.date;
	if ( date ) {
		date = this.dateFromValue( date );
		this.repeatEnd.setValue( this.formatDateForInput( this.getDataForInterval( date, repeatConfig.repeatInterval || 'd' ) ) );
	}
	if ( repeatConfig.hasOwnProperty( 'weekdaysToRepeat' ) ) {
		const daysToRepeat = repeatConfig.weekdaysToRepeat || [];
		for ( let i = 0; i < daysToRepeat.length; i++ ) {
			const item = this.repeatDaysOfWeek.findItemFromData( daysToRepeat[ i ] );
			if ( item ) {
				item.setValue( true );
			}
		}
	}
	if ( repeatConfig.hasOwnProperty( 'monthlyRepeatInterval' ) ) {
		this.repeatMonthInterval.setValue( repeatConfig.monthlyRepeatInterval );
	}
};

bs.reminder.ui.mixin.RepeatLayout.prototype.getDataForInterval = function ( date, repeatInterval ) {
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
		date.setYear( date.getFullYear() + 1 );
	}

	return date;
};

bs.reminder.ui.mixin.RepeatLayout.prototype.getDayOfTheMonthIntervalOption = function ( date ) {
	return {
		data: 'dayOfTheMonth',
		label: mw.message( 'bs-reminder-monthly-on-day-prefix' ).text() + ' ' +
			date.getDate()
	};
};

bs.reminder.ui.mixin.RepeatLayout.prototype.getDayOfTheWeekIntervalOption = function ( date ) {
	const currentDayNumeric = date.getDay();
	const currentDayText = date.toLocaleString( mw.config.get( 'wgUserLanguage' ), {
		weekday: 'long'
	} );
	const daysInCurrentMonth = new Date( date.getYear(), date.getMonth(), 0 ).getDate();
	let weekOrderNum = Math.ceil( currentDayNumeric / 7 );
	let weekOrder;

	if ( daysInCurrentMonth - currentDayNumeric < 7 ) {
		weekOrder = mw.message( 'bs-reminder-ordinal-last' ).text();
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
		label: mw.message( 'bs-reminder-monthly-on-the-prefix' ).text() +
			' ' + weekOrder + ' ' + currentDayText
	};
};

bs.reminder.ui.mixin.RepeatLayout.prototype.mapNumbersToOrdinals = function ( number ) {
	switch ( number ) {
		case 1:
			return mw.message( 'bs-reminder-ordinal-first' ).text();
		case 2:
			return mw.message( 'bs-reminder-ordinal-second' ).text();
		case 3:
			return mw.message( 'bs-reminder-ordinal-third' ).text();
		case 4:
			return mw.message( 'bs-reminder-ordinal-fourth' ).text();
	}
};
