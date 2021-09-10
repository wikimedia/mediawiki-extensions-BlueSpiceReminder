Ext.define( "BS.Reminder.BaseDialog", {
	extend: "MWExt.Dialog",
	modal: true,
	title: mw.message( 'bs-reminder-create-title' ).plain(),
	makeItems: function () {
		this.dfDate = Ext.create( 'Ext.form.field.Date', {
			fieldLabel: mw.message( 'bs-reminder-date-label' ).plain(),
			value: new Date( mw.config.get( 'DefaultReminderPeriod' ) * 1000 ),
			minValue: new Date( ),
			labelAlign: 'right',
			name: 'df-date',
			format: "d.m.Y"
		} );
		this.taComment = Ext.create( 'Ext.form.field.TextArea', {
			fieldLabel: mw.message( 'bs-reminder-comment-label' ).plain(),
			labelAlign: 'right',
			value: '',
			maxLength: 255
		});
		this.hfId = Ext.create( 'Ext.form.field.Hidden', {
			value: '',
			name: 'hf-id'
		} );
		return [
			this.dfDate,
			this.taComment,
			this.hfId
		];
	},
	setData: function ( obj ) {
		this.dfDate.setValue( obj.date );
		this.hfId.setValue( obj.id );
		this.cbTargetPage.setValue( obj.page_title );
		this.cbxUser.setValue( obj.userName );
		this.taComment.setValue( obj.comment );
	},
	getDataForId: function ( iArticleId ) {
		var me = this;
		var api = new mw.Api();
		api.postWithToken( 'csrf', {
			action: 'bs-reminder-tasks',
			task: 'getDetailsForReminder',
			taskData: Ext.encode( {
				'articleId': iArticleId
			} )
		})
		.fail(function( protocol, response ) {
			bs.util.alert(
				'bs-reminder-delete-error-unknown',
				{
					text: response.exception
				}
			);
		})
		.done(function( response, xhr ){
			if ( typeof ( response.payload.id ) !== "undefined" ) {
				me.setData( response.payload );
			}
		});
	},
	getData: function () {
		var articleId = false;
		if ( this.cbTargetPage.getValue() ) {
			articleId = this.cbTargetPage.getValue().get( 'page_id' );
		}
		var obj = {
			//format to MWTimestamp
			date: Ext.Date.format( this.dfDate.getValue(), 'YmdHis' ),
			id: this.hfId.getValue(),
			articleId: articleId,
			userName: this.cbxUser.getValue(),
			comment: this.taComment.getValue()
		};
		$( document ).trigger( "BSReminderGetData", [ this, obj ] );
		return obj;
	}
});