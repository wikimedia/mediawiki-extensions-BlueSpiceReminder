( ( mw, bs ) => {
	bs.util.registerNamespace( 'bs.reminder.info' );

	bs.reminder.info.ReminderInformationPage = function ReminderInformationPage( name, config ) {
		this.reminderGrid = null;
		bs.reminder.info.ReminderInformationPage.super.call( this, name, config );
	};

	OO.inheritClass( bs.reminder.info.ReminderInformationPage, StandardDialogs.ui.BasePage ); // eslint-disable-line no-undef

	bs.reminder.info.ReminderInformationPage.prototype.setupOutlineItem = function () {
		bs.reminder.info.ReminderInformationPage.super.prototype.setupOutlineItem.apply( this, arguments );

		if ( this.outlineItem ) {
			this.outlineItem.setLabel( mw.message( 'bs-reminder-info-dialog' ).plain() );
		}
	};

	bs.reminder.info.ReminderInformationPage.prototype.setup = function () {
		return;
	};

	bs.reminder.info.ReminderInformationPage.prototype.onInfoPanelSelect = async function () {
		if ( !this.reminderGrid ) {
			await mw.loader.using( [ 'ext.oOJSPlus.data', 'oojs-ui.styles.icons-user' ] );

			const reminderStore = new OOJSPlus.ui.data.store.RemoteStore( {
				action: 'bs-reminder-store',
				pageSize: 25
			} );
			reminderStore.filter( new OOJSPlus.ui.data.filter.String( {
				value: this.pageName.replace( /_/g, ' ' ),
				operator: 'eq',
				type: 'string'
			} ), 'page_title' );

			const specialPageButton = new OO.ui.ButtonWidget( {
				label: mw.message( 'bs-reminder-info-dialog-button-label' ).text(),
				href: mw.util.getUrl( 'Special:Reminder' )
			} );

			this.reminderGrid = new OOJSPlus.ui.data.GridWidget( {
				selectable: false,
				sortable: false,
				orderable: false,
				columns: {
					user_name: { // eslint-disable-line camelcase
						headerText: mw.message( 'bs-reminder-header-username' ).text(),
						type: 'user',
						showImage: true
					},
					reminder_date: { // eslint-disable-line camelcase
						headerText: mw.message( 'bs-reminder-header-date' ).text(),
						type: 'text'

					},
					rem_comment: { // eslint-disable-line camelcase
						headerText: mw.message( 'bs-reminder-header-comment' ).text(),
						type: 'text'
					}
				},
				store: reminderStore,
				tools: [ specialPageButton ]
			} );

			this.$element.append( this.reminderGrid.$element );
		}
	};

	registryPageInformation.register( 'reminder_infos', bs.reminder.info.ReminderInformationPage ); // eslint-disable-line no-undef

} )( mediaWiki, blueSpice );
