Ext.define( 'BS.Reminder.flyout.dataview.Upcoming', {
	extend: 'Ext.DataView',
	requires: [ 'BS.store.BSApi' ],
	cls: 'bs-reminder-flyout-upcoming',
	articleId: mw.config.get( 'wgArticleId' ),
	userName: mw.config.get( 'wgUserName' ),
	initComponent: function() {
		var yesterday = new Date();
		yesterday.setDate( yesterday.getDate() - 1 );
		//Make date in format expected by the store - month + 1 because Jan = 0
		//This is necessary until there is a mechanism of dismissing acknowledged reminders
		var formattedYesterday =
			( yesterday.getMonth() + 1 ) + '/' + yesterday.getDate() + '/' + yesterday.getFullYear();


		this.store = new BS.store.BSApi( {
			apiAction: 'bs-reminder-store',
			autoLoad: true,
			remoteSort: true,
			fields: [ 'id', 'user_name', 'article_id', 'reminder_date', 'rem_comment' ],
			proxy: {
				extraParams: {
					limit: 1
				}
			},
			filters: [{
				property: 'article_id',
				type: 'numeric',
				comparison: 'eq',
				value: this.articleId
			}, {
				property: 'user_name',
				type: 'string',
				comparison: 'eq',
				value: this.userName
			}, {
				property: 'reminder_date',
				type: 'date',
				comparison: 'gt',
				value: formattedYesterday
			}],
			sorters: [{
				property: 'reminder_date',
				direction: 'ASC'
			}]
		} );

		this.itemTpl = new Ext.XTemplate(
			'<p class="{reminder_date:this.isDue}">',
			"{reminder_date:this.getMessage}",
			'</p>', {
				remainingDays: function( reminder_date ) {
					var today = new Date();
					var remDate = new Date( reminder_date );
					var msTime = remDate.getTime() - today.getTime();
					var daysRemaining = Math.ceil( msTime / ( 1000 * 60 * 60 * 24 ) );
					return daysRemaining;
				},
				isDue: function( reminder_date ) {
					if( this.remainingDays( reminder_date ) === 0 ) {
						return "reminder-due";
					}
				},
				getMessage: function( reminder_date ) {
					var remaningDays = this.remainingDays( reminder_date );
					if( remaningDays <= 0 ) {
						return mw.message( 'bs-reminder-flyout-upcoming-due' ).plain();
					}

					return mw.message( 'bs-reminder-flyout-upcoming', remaningDays ).parse();
				}
			}
		);

		this.emptyText = mw.message( 'bs-reminder-flyout-upcoming-none' ).escaped();

		this.callParent( arguments );
	}
} );
