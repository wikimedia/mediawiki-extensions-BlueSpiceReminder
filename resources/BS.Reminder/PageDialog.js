Ext.define( "BS.Reminder.PageDialog", {
	extend: "BS.Reminder.BaseDialog",
	makeItems: function () {
		var items = this.callParent( arguments );

		this.cbTargetPage = Ext.create( 'Ext.form.field.Hidden', {
			value: ''
		});
		this.cbxUser = Ext.create( 'Ext.form.field.Hidden', {
			value : ''
		});

		items.push( this.cbTargetPage );
		items.push( this.cbxUser );

		return items;
	},
	getData: function () {
		var obj = {
			//format to MWTimestamp
			date: Ext.Date.format( this.dfDate.getValue(), 'YmdHis' ),
			id: this.hfId.getValue(),
			articleId: this.cbTargetPage.getValue(),
			userName: this.cbxUser.getValue(),
			comment: this.taComment.getValue()
		};
		$( document ).trigger( "BSReminderGetData", [ this, obj ] );
		return obj;
	},
	setData: function ( obj ) {
		this.callParent( arguments );
		this.cbTargetPage.setValue( obj.articleId );
	},
	makeFooterButtons: function() {
		this.callParent( arguments );

		this.btnManager = Ext.create( 'Ext.Button', {
			text: mw.message( 'bs-reminder-dlg-btn-manager-label' ).plain(),
			id: this.getId()+'-btn-manager'
		});
		this.btnManager.on( 'click', this.onBtnManagerClick, this );

		return [
			this.btnManager
		];
	},
	onBtnManagerClick: function() {
		var url = mw.util.getUrl( "Special:Reminder/" );
		window.location = url;
	}
});