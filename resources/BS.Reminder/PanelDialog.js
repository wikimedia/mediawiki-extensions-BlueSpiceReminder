Ext.define( "BS.Reminder.PanelDialog", {
	extend: "BS.Reminder.BaseDialog",
	makeItems: function () {
		var items = this.callParent( arguments );
		this.usersStore = Ext.create( 'Ext.data.Store', {
			fields: [
				'userid', 'name'
			],
			proxy: {
				type: 'ajax',
				url: mw.util.wikiScript( 'api' ),
				reader: {
					type: 'json',
					rootProperty: 'query.allusers',
					idProperty: 'userid'
				},
				extraParams: {
					format: 'json',
					action: 'query',
					list: 'allusers',
					aulimit: 'max',
				}
			},
			autoLoad: true
		});

		this.cbTargetPage = Ext.create( 'BS.form.field.TitleCombo', {
			fieldLabel: mw.message( 'bs-reminder-article-label' ).plain(),
			labelAlign: 'right'
		});
		this.cbxUser = Ext.create( 'Ext.form.field.ComboBox', {
			fieldLabel: mw.message( 'bs-reminder-user-label' ).plain(),
			labelAlign: 'right',
			store: this.usersStore,
			displayField: 'name',
			valueField: 'userid',
			typeAhead: true
		} );

		items.push( this.cbTargetPage );
		items.push( this.cbxUser );

		return items;
	}
});