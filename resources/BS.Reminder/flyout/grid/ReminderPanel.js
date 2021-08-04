Ext.define( 'BS.Reminder.flyout.grid.ReminderPanel', {
	extend: 'Ext.grid.Panel',
	requires: [ 'BS.store.BSApi', 'BS.Reminder.flyout.model.ReminderListItem' ],
	title: mw.message( 'bs-reminder-flyout-grid-title' ).plain(),
	cls: 'bs-reminder-flyout-grid',
	maxWidth: 600,
	minHeight: 260,
	pageSize : 5,
	articleId: mw.config.get( 'wgArticleId' ),
	userName: mw.config.get( 'wgUserName' ),
	hideHeaders: true,
	initComponent: function() {
		this.store = new BS.store.BSApi( {
			apiAction: 'bs-reminder-store',
			autoLoad: true,
			remoteSort: true,
			model: 'BS.Reminder.flyout.model.ReminderListItem',
			pageSize: this.pageSize,
			proxy: {
				extraParams: {
				}
			},
			filters: [{
				property: 'article_id',
				type: 'numeric',
				comparison: 'eq',
				value: this.articleId
			}],
			sorters: [{
				property: 'reminder_date',
				direction: 'DESC'
			}]
		} );

		this.colAggregatedInfo = Ext.create( 'Ext.grid.column.Template', {
			id: 'reminder-aggregated',
			sortable: false,
			width: 600,
			height: 500,
			tpl: "<div><img class='bs-reminder-flyout-grid-user-image' src='{user_image_url}' /><span>{reminder_date} {user_name}</span></div>",
			flex: 1
		} );

		this.colUserName = Ext.create( 'Ext.grid.column.Template', {
			id: 'user_name',
			header: mw.message( 'bs-reminder-header-username' ).plain(),
			sortable: true,
			dataIndex: 'user_name',
			tpl: '<a data-bs-username="{user_name}" href="{user_page}">{user_name}</a>',
			filter: {
				type: 'string'
			},
			hidden: true
		} );

		this.colReminderDate = Ext.create( 'Ext.grid.column.Column', {
			id: 'reminder_date',
			header: mw.message('bs-reminder-header-date').plain(),
			sortable: true,
			dataIndex: 'reminder_date',
			hidden: true,
			filter: {
				type: 'date'
			}
		} );

		this.colComment = Ext.create( 'Ext.grid.column.Column', {
			id: 'rem_comment',
			header: mw.message('bs-reminder-header-comment').plain(),
			sortable: false,
			hidden: true,
			dataIndex: 'rem_comment',
			filter: {
				type: 'string'
			}
		});

		this.columns = [
			this.colAggregatedInfo,
			this.colUserName,
			this.colReminderDate,
			this.colComment
		];

		this.bbar = new Ext.toolbar.Paging( {
			store: this.store
		} );

		this.callParent( arguments );
	}
} );
