(function( mw, $, d, bs, undefined ){
	bs.util.registerNamespace( 'bs.reminder.ui' );
	bs.util.registerNamespace( 'bs.reminder.ui.mixin' );

	function _showDialog() {
		var dfd = $.Deferred();
		bs.reminder.canEditAll().done( function() {
				dfd.resolve( getPages( true ) );
		} ).fail( function() {
			dfd.resolve( getPages( false ) );
		} );

		function getPages( canEditAll ) {
			return [
				new bs.reminder.ui.CreateReminderForPage( {
					data: {
						page: mw.config.get( 'wgPageName' ),
						user: mw.config.get( 'wgUserName' ),
						date: new Date( mw.config.get( 'DefaultReminderPeriod' ) * 1000 ),
					},
					canCreateForOthers: canEditAll
				} )
			];
		}

		var dialog = new OOJSPlus.ui.dialog.BookletDialog( {
			id: 'bs-reminder-dialog-create',
			pages: function() {
				return dfd.promise();
			}
		} );

		dialog.show();
	};

	bs.reminder.canEditAll = function() {
		var dfd = $.Deferred();

		mw.user.getRights().done( function( rights ) {
			if ( rights.indexOf( 'remindereditall' ) !== -1 ) {
				dfd.resolve();
			} else {
				dfd.reject();
			}
		} );

		return dfd.promise();
	};

	$( d ).on( 'click', "#ca-reminderCreate, .ca-reminderCreate", function ( e ) {
		e.preventDefault();
		mw.loader.using( 'ext.bluespice.reminder.dialog.pages' ).done( _showDialog );
	} );
})( mediaWiki, jQuery, document, blueSpice );
