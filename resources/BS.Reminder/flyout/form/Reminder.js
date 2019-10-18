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
			format: "d.m.Y"
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

		this.cbxUser = Ext.create( 'BS.form.UserCombo', {
			hideLabel: true,
			valueField: 'user_name'
		} );

		this.cbxUser.setValue( this.userName );

		this.hfId = Ext.create( 'Ext.form.field.Hidden', {
			value: '',
			name: 'hf-id'
		} );

		this.btnSave = Ext.create("Ext.button.Button", {
			id: "bs-reminder-flyout-reminder-form-save-btn",
			text: mw.message('bs-extjs-save').plain(),
			handler: this.onBtnSaveClick,
			flex: 0.5,
			scope: this
		});

		this.btnCancel = Ext.create("Ext.button.Button", {
			id: "bs-reminder-flyout-reminder-form-cancel-btn",
			text: mw.message('bs-extjs-reset').plain(),
			handler: this.onBtnCancelClick,
			flex: 0.5,
			scope: this,
			disabled: true
		});

		this.items = [
			this.dfDate,
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
			date: Ext.Date.format( this.dfDate.getValue(), 'YmdHis' )
		}

		$( document ).trigger( "BSReminderGetData", [ this, obj ] );
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
		this.fireEvent( 'save', this, this.getData() );
	},
	onBtnCancelClick: function( btn, e ) {
		this.reset();
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
	}

});
