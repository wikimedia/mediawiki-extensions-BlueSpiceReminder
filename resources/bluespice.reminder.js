(function( mw, $, d, bs, undefined ){
	$(d).on( 'click', "#ca-reminderCreate, .ca-reminderCreate", function ( e ) {
		e.preventDefault();
		var me = this;
		mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
			Ext.onReady( function() {
				if ( !me.dlgReminder ) {
					me.dlgReminder = Ext.create( 'BS.Reminder.PageDialog', {id: 'bs-reminder-dlg-reminder' });
					me.dlgReminder.on( 'ok', function() {
						var obj = me.dlgReminder.getData();
						bs.api.tasks.exec(
							'reminder',
							'saveReminder',
							obj
						);
					}, me );
				}
				var obj = {
					articleId: mw.config.get( 'wgArticleId' ),
					date: new Date( mw.config.get( 'DefaultReminderPeriod' ) * 1000 ),
					calledFromArticle: true
				};
				me.dlgReminder.setData( obj );
				me.dlgReminder.show( me );
			} );
		});
	} );

})( mediaWiki, jQuery, document, blueSpice );