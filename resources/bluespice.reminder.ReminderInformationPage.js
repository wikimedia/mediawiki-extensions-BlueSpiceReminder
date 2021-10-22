(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.reminder.info' );

	bs.reminder.info.ReminderInformationPage = function ReminderInformationPage( name, config ) {
		this.reminderGrid = null;
		bs.reminder.info.ReminderInformationPage.super.call( this, name, config );
	};

	OO.inheritClass( bs.reminder.info.ReminderInformationPage, StandardDialogs.ui.BasePage );

	bs.reminder.info.ReminderInformationPage.prototype.setupOutlineItem = function () {
		bs.reminder.info.ReminderInformationPage.super.prototype.setupOutlineItem.apply( this, arguments );

		if ( this.outlineItem ) {
			this.outlineItem.setLabel( 'Reminder' );
		}
	};

	bs.reminder.info.ReminderInformationPage.prototype.setup = function () {
		return;
	};

	bs.reminder.info.ReminderInformationPage.prototype.onInfoPanelSelect = function () {
		var me = this;
		if ( me.reminderGrid === null ) {
			mw.loader.using( 'ext.bluespice.extjs').done( function () {
				Ext.onReady( function( ) {
					me.reminderGrid = Ext.create( 'BS.Reminder.flyout.grid.ReminderPanel', {
						title: false,
						renderTo: me.$element[0],
						width: me.$element.width(),
						height: me.$element.height()
						});
				}, me );
			});
		}
	}

	bs.reminder.info.ReminderInformationPage.prototype.getData = function () {
		var dfd = new $.Deferred();
		mw.loader.using( 'ext.bluespice.extjs').done( function () {
			Ext.require( 'BS.Reminder.flyout.grid.ReminderPanel', function() {
				dfd.resolve();
			});
		});
		return dfd.promise();
	};

	// register
	registryPageInformation.register( 'reminder_infos', bs.reminder.info.ReminderInformationPage );

})( mediaWiki, jQuery, blueSpice );
