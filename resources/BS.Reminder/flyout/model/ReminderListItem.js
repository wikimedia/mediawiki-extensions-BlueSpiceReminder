Ext.define( 'BS.Reminder.flyout.model.ReminderListItem', {
	extend: 'Ext.data.Model',
	fields: [
		{ name: 'user_image_url', type: 'string', convert: function( val, record ) {
			return mw.config.get('wgScriptPath')
				+ '/dynamic_file.php'
				+ '?module=userprofileimage'
				+ '&username=' + record.data.user_name
				;
		} },
		{ name: 'user_name', type: 'string' },
		{ name: 'article_id', type: 'string' },
		{ name: 'reminder_date', type: 'string' },
		{ name: 'rem_comment', type: 'string' }
	]
} );
