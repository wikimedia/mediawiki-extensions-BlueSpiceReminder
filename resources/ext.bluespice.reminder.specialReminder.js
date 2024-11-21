( ( $ ) => {

	$( () => {
		const $container = $( '#bs-reminder-special-reminder-container' ); // eslint-disable-line no-jquery/no-global-selector
		if ( $container.length === 0 ) {
			return;
		}

		const panel = new ext.bluespice.reminder.ui.panel.SpecialReminderPanel();

		$container.append( panel.$element );
	} );

} )( jQuery );
