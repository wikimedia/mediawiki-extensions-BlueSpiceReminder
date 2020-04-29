Ext.define( 'BS.Reminder.flyout.form.Reminder', {
	extend: 'Ext.form.Panel',
	cls: 'bs-reminder-flyout-form',
	title: mw.message( 'bs-reminder-flyout-form-title' ).plain(),
	articleId: mw.config.get( 'wgArticleId' ),
	date: new Date( mw.config.get( 'DefaultReminderPeriod' ) * 1000 ),
	userName: mw.config.get( 'wgUserName' ),
	fieldDefaults: {
		anchor: '100%'
	},
	initComponent: function() {
		this.on( 'dirtychange', this.onDirtyChange, this );

		this.dfDate = Ext.create( 'Ext.form.field.Date', {
			emptyText: mw.message( 'bs-reminder-date-label' ).plain(),
			value: this.date,
			minValue: new Date( ),
			labelAlign: 'right',
			name: 'df-date',
			format: 'd.m.Y'
		} );

		this.taComment = Ext.create( 'Ext.form.field.TextArea', {
			emptyText: mw.message( 'bs-reminder-comment-label' ).plain(),
			labelAlign: 'right',
			value: '',
			maxLength: 255
		});

		this.cbTargetPage = Ext.create( 'Ext.form.field.Hidden', {
			value: this.articleId
		});

		this.cbxIsRepeating = Ext.create( 'Ext.form.field.Checkbox', {
			valueField: 'value',
			displayField: 'msg',
			boxLabel: mw.message( 'bs-reminder-repeat-label' ).plain(),
			inputValue: true
		} );

		this.cbxIsRepeating.on( 'change', this.onDoRepeatChange, this );

		this.cbxIntervalType = Ext.create( 'Ext.form.ComboBox', {
			valueField: 'value',
			displayField: 'msg',
			value: 'd',
			queryMode: 'local',
			store: Ext.create('Ext.data.Store', {
				fields: ['value', 'msg'],
				data : [
					{ 'value': 'd', 'msg': mw.message( 'bs-reminder-repeat-interval-day' ).plain() },
					{ 'value': 'w', 'msg': mw.message( 'bs-reminder-repeat-interval-week' ).plain() },
					{ 'value': 'm', 'msg': mw.message( 'bs-reminder-repeat-interval-month' ).plain() },
					{ 'value': 'y', 'msg': mw.message( 'bs-reminder-repeat-interval-year' ).plain() }
				]
			})
		} );

		this.cbxIntervalType.on( 'select', this.onIntervalTypeSelect, this );

		this.cbxMonthlyRepeatInterval = Ext.create( 'Ext.form.ComboBox', {
			valueField: 'value',
			displayField: 'msg',
			hidden: true,
			queryMode: 'local',
			selectOnFocus:true
		} );

		this.storeMonthlyRepeatInterval = Ext.create('Ext.data.Store', {
			fields: ['value', 'msg']
		});

		this.btgWeekDaysCheckboxes = Ext.create( 'Ext.container.ButtonGroup', {
			width: '100%',
			columns: 7,
			hidden: true,
			layout: {
				type: 'hbox',
				align: 'center'
			},
			title: mw.message( 'bs-reminder-repeat-on-title' ).plain(),
			defaults: {
				enableToggle: true,
				width: '14%'
			},
			margin: '0 0 10px 0',
			items: [
				{
					text: mw.message( 'bs-reminder-sunday-abbr' ).plain(),
					inputValue: 0
				},
				{
					text: mw.message( 'bs-reminder-monday-abbr' ).plain(),
					inputValue: 1
				},
				{
					text: mw.message( 'bs-reminder-tuesday-abbr' ).plain(),
					inputValue: 2
				},
				{
					text: mw.message( 'bs-reminder-wednesday-abbr' ).plain(),
					inputValue: 3
				},
				{
					text: mw.message( 'bs-reminder-thursday-abbr' ).plain(),
					inputValue: 4
				},
				{
					text: mw.message( 'bs-reminder-friday-abbr' ).plain(),
					inputValue: 5
				},
				{
					text: mw.message( 'bs-reminder-saturday-abbr' ).plain(),
					inputValue: 6
				}
			]
		});

		this.dfRepeatDateEnd = Ext.create( 'Ext.form.field.Date', {
			fieldLabel: mw.message( 'bs-reminder-date-repeat-ends-on-label' ).plain(),
			value: Ext.Date.add( this.dfDate.value, Ext.Date.DAY, 1 ),
			minValue: this.dfDate.value,
			labelAlign: 'right',
			name: 'df-date',
			format: 'd.m.Y'
		} );

		this.dfDate.on( 'select', this.onDateSelect, this );

		this.inpIntervalValue = new Ext.form.field.Number( {
			name: 'interval_value',
			minValue: 1,
			value: 1
		} );

		this.pnlInterval = Ext.create( 'Ext.panel.Panel', {
			hidden: true,
			layout: {
				type: 'vbox'
			},
			items: [
				new Ext.panel.Panel( {
					title: mw.message( 'bs-reminder-repeat-every-title' ).plain(),
					layout: 'hbox',
					align: 'left',
					padding: '0 0 10px 0',
					items: [
						this.inpIntervalValue,
						this.cbxIntervalType
					]
				}),
				this.cbxMonthlyRepeatInterval,
				this.btgWeekDaysCheckboxes,
				this.dfRepeatDateEnd,
			]
		} );

		this.cbxUser = Ext.create( 'BS.form.UserCombo', {
			hideLabel: true,
			valueField: 'user_name'
		} );

		this.cbxUser.setValue( this.userName );

		this.hfId = Ext.create( 'Ext.form.field.Hidden', {
			value: '',
			name: 'hf-id'
		} );

		this.btnSave = Ext.create('Ext.button.Button', {
			id: 'bs-reminder-flyout-reminder-form-save-btn',
			text: mw.message('bs-extjs-save').plain(),
			handler: this.onBtnSaveClick,
			flex: 0.5,
			scope: this
		});

		this.btnCancel = Ext.create('Ext.button.Button', {
			id: 'bs-reminder-flyout-reminder-form-cancel-btn',
			text: mw.message('bs-extjs-reset').plain(),
			handler: this.onBtnCancelClick,
			flex: 0.5,
			scope: this,
			disabled: true
		});

		this.items = [
			this.dfDate,
			this.cbxIsRepeating,
			this.pnlInterval,
			this.cbxUser,
			this.taComment,
			this.hfId,
			this.cbTargetPage
		];

		this.buttons = [
			this.btnSave,
			this.btnCancel
		];

		this.callParent(arguments);
	},
	getData: function() {
		var obj = {
			articleId: this.cbTargetPage.getValue(),
			userName: this.cbxUser.getValue(),
			comment: this.taComment.getValue(),
			id: this.hfId.getValue(),
			date: Ext.Date.format( this.dfDate.getValue(), 'YmdHis' ),
			isRepeating: this.cbxIsRepeating.getValue(),
			repeatDateEnd: Ext.Date.format( this.dfRepeatDateEnd.getValue(), 'YmdHis' ),
			repeatConfig: {
				intervalType: this.cbxIntervalType.getValue(),
				intervalValue: this.inpIntervalValue.getValue(),
				weekdaysToRepeat: [],
				monthlyRepeatInterval: this.cbxMonthlyRepeatInterval.getValue()
			}
		};

		if ( obj.isRepeating === true && obj.repeatConfig.intervalType === 'w' ) {
			var weekDays = this.btgWeekDaysCheckboxes.items.items;
			for(var i = 0; i < weekDays.length; i++) {
				if ( weekDays[i].pressed === true ) {
					obj.repeatConfig.weekdaysToRepeat.push( weekDays[i].inputValue );
				}
			}
		}

		$( document ).trigger( 'BSReminderGetData', [ this, obj ] );
		return obj;
	},
	setData: function( obj ) {
		this.dfDate.setValue( obj.date );
		this.hfId.setValue( obj.id );
		this.taComment.setValue( obj.comment );
		this.cbxUser.setValue( obj.userName );
		this.cbTargetPage.setValue( obj.articleId );
	},
	onBtnSaveClick: function( btn, e ) {
		this.pnlInterval.hide();
		this.hideIntervalOptions();
		this.fireEvent( 'save', this, this.getData() );
	},
	onBtnCancelClick: function( btn, e ) {
		this.reset();
		this.pnlInterval.hide();
		this.hideIntervalOptions();
	},
	onDirtyChange: function( sender, dirty ) {
		this.setButtonsDisabled( !dirty );
	},
	setButtonsDisabled: function( disabled ) {
		if( disabled ) {
			this.btnSave.disable();
			this.btnCancel.disable();
		} else {
			this.btnSave.enable();
			this.btnCancel.enable();
		}
	},
	onDoRepeatChange: function( field, newValue, oldValue, eOpts ) {
		if ( newValue === true ) {
			this.updateMonthlyRepeatIntervalStore( this.dfDate.getValue() );
			this.pnlInterval.show();
		} else {
			this.pnlInterval.hide();
		}
	},
	onIntervalTypeSelect: function( field, record ) {
		this.hideIntervalOptions();
		switch( record.get('value') ) {
			case 'm':
				this.cbxMonthlyRepeatInterval.show();
				break;
			case 'w':
				this.btgWeekDaysCheckboxes.show();
				break;
		}
	},
	hideIntervalOptions: function() {
		this.cbxMonthlyRepeatInterval.hide();
		this.btgWeekDaysCheckboxes.hide();
	},
	updateMonthlyRepeatIntervalStore: function( date ) {
		this.storeMonthlyRepeatInterval.removeAll();
		this.storeMonthlyRepeatInterval.add( this.getDayOfTheMonthIntervalOption( date ) );
		this.storeMonthlyRepeatInterval.add( this.getDayOfTheWeekIntervalOption( date ) );
		this.cbxMonthlyRepeatInterval.bindStore( this.storeMonthlyRepeatInterval );
		this.cbxMonthlyRepeatInterval.select( this.cbxMonthlyRepeatInterval.getStore().getAt(0) );
	},
	getDayOfTheMonthIntervalOption: function( date ) {
		return {
			value: {
				type: 'dayOfTheMonth'
			},
			msg: mw.message( 'bs-reminder-monthly-on-day-prefix' ).plain() + ' ' +
				Ext.Date.format( date, 'j' )
		};
	},
	getDayOfTheWeekIntervalOption: function( date ) {
		var currentDayNumeric = parseInt( Ext.Date.format( date, 'j' ) );
		var currentDayText = Ext.Date.format( date, 'l');
		var daysInCurrentMonth = parseInt( Ext.Date.format( date, 't' ) );
		var weekOrderNum = Math.ceil( currentDayNumeric / 7 );
		var weekOrder;

		if ( daysInCurrentMonth - currentDayNumeric < 7 ) {
			weekOrder = mw.message( 'bs-reminder-ordinal-last' ).plain();
			weekOrderNum = -1;
		} else {
			weekOrder = this.mapNumbersToOrdinals( weekOrderNum );
		}
		return {
			value: {
				type: 'dayOfTheWeek',
				weekOrder: weekOrderNum,
				weekdayOrder: parseInt( Ext.Date.format( date, 'w' ) )
			},
			msg: mw.message( 'bs-reminder-monthly-on-the-prefix' ).plain() +
				' ' + weekOrder + ' ' + currentDayText
		};
	},
	mapNumbersToOrdinals: function( number ) {
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
	},
	onDateSelect: function( field, value ) {
		if ( value > this.dfRepeatDateEnd.getValue() ) {
			this.dfRepeatDateEnd.setValue( Ext.Date.add( value, Ext.Date.DAY, 1 ) );
			this.dfRepeatDateEnd.setMinValue( value );
		}
		this.updateMonthlyRepeatIntervalStore( value );
	}
});
