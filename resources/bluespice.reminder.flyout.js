(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.reminder' );
	bs.reminder.flyoutCallback = function( $body ) {
		var dfd = $.Deferred();
		$( function() {
			Ext.create( 'BS.Reminder.flyout.Base', {
				renderTo: $body[0]
			} );
		} );

		dfd.resolve();
		return dfd.promise();
	};

})( mediaWiki, jQuery, blueSpice );
