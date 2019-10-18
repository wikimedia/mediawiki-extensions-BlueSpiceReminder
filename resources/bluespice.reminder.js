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

	$(d).on( 'BSExpiryInitCreateForm', function( self, dlg ){
		dlg.cbxCreateReminder = Ext.create( 'Ext.form.field.Checkbox', {
			fieldLabel: mw.message( 'bs-reminder-create-reminder-label' ).plain()
		});
		dlg.items.push( dlg.cbxCreateReminder );
	});
	$(d).on( 'BSExpiryGetData', function( self, dlg, obj ){
		var localObj = {
			setReminder: dlg.cbxCreateReminder.getValue()
		};
		obj = Ext.merge( obj, localObj );
	});
	$(d).on( 'BSExpiryAddOk', function( self, panel, obj ) {
		if ( obj.setReminder === true ) {
			bs.api.tasks.exec(
				'reminder',
				'saveReminder',
				obj
			)
		}
	});
	$(d).on( 'BSExpiryEditOk', function( self, panel, obj ) {
		if ( obj.setReminder === true ) {
			bs.api.tasks.exec(
				'reminder',
				'saveReminder',
				obj
			)
		}
	});
})( mediaWiki, jQuery, document, blueSpice );