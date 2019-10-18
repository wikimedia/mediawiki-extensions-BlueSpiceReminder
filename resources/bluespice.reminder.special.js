/**
 * Reminder extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @version    2.22.0
 * @package    Bluespice_Extensions
 * @subpackage Reminder
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.onReady( function(){
	var taskPermissions = mw.config.get( 'bsTaskAPIPermissions' );
	var operationPermissions = {
		create: true, //should be connected to mw.config.get('bsTaskAPIPermissions').extension_xyz.task1 = boolean in derived class
		update: true, //...
		delete: true  //...
	};
	if ( taskPermissions !== null && taskPermissions.hasOwnProperty( 'reminder' ) ) {
		if ( typeof taskPermissions.reminder.saveReminder === 'boolean' ) {
			operationPermissions.create = taskPermissions.reminder.saveReminder;
			operationPermissions.update = taskPermissions.reminder.saveReminder;
		}
		if ( typeof taskPermissions.reminder.deleteReminder === 'boolean' ) {
			operationPermissions.delete = taskPermissions.reminder.deleteReminder;
		}
	}

	Ext.create( 'BS.Reminder.Panel', {
		operationPermissions: operationPermissions,
		renderTo: 'bs-reminder-overview-grid'
	} );
} );