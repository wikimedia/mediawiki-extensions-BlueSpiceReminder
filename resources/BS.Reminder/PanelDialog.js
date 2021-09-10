Ext.define( "BS.Reminder.PanelDialog", {
	extend: "BS.Reminder.BaseDialog",
	makeItems: function () {
		var items = this.callParent( arguments );

		this.cbTargetPage = Ext.create( 'BS.form.field.TitleCombo', {
			fieldLabel: mw.message( 'bs-reminder-article-label' ).plain(),
			labelAlign: 'right'
		});

		this.cbxUser = Ext.create( 'BS.form.UserCombo', {
			valueField: 'user_name'
		} );

		items.push( this.cbTargetPage );
		items.push( this.cbxUser );

		return items;
	}
});