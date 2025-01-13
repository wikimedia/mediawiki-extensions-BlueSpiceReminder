bs.util.registerNamespace( 'ext.bluespice.reminder.ui.panel' );

ext.bluespice.reminder.ui.panel.SpecialReminderPanel = function ( cfg ) {
	cfg = cfg || {};

	/** @description Stores the username if set; otherwise, the value indicates that the user has `remindereditall` permission. */
	this.username = mw.config.get( 'BSReminderUsername' );
	const page = mw.config.get( 'BSReminderPage' );

	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-reminder-store',
		pageSize: 25
	} );

	if ( this.username ) {
		this.store.filter(
			new OOJSPlus.ui.data.filter.String( {
				value: this.username,
				operator: 'eq',
				type: 'string'
			} ),
			'user_name'
		);
	} else if ( page ) {
		this.store.filter(
			new OOJSPlus.ui.data.filter.String( {
				value: page,
				operator: 'eq',
				type: 'string'
			} ),
			'page_title'
		);
	}

	cfg.grid = this.setupGridConfig();

	ext.bluespice.reminder.ui.panel.SpecialReminderPanel.parent.call( this, cfg );
};

OO.inheritClass( ext.bluespice.reminder.ui.panel.SpecialReminderPanel, OOJSPlus.ui.panel.ManagerGrid );

ext.bluespice.reminder.ui.panel.SpecialReminderPanel.prototype.setupGridConfig = function () {
	const gridCfg = {
		multiSelect: false,
		exportable: true,
		style: 'differentiate-rows',
		columns: {
			user_name: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-reminder-header-username' ).plain(),
				type: 'user',
				showImage: true,
				sortable: true,
				filter: { type: 'user' },
				hidden: this.username
			},
			page_title: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-reminder-header-pagename' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( value ) => {
					return new OO.ui.HtmlSnippet( mw.html.element(
						'a',
						{
							href: mw.util.getUrl( value )
						},
						value
					) );
				}
			},
			rem_type: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-reminder-header-type' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( value ) => {
					value = value || 'page';
					return mw.config.get( 'bsgReminderRegisteredTypes' )[ value ].LabelMsg;
				}
			},
			reminder_date: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-reminder-header-date' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'date' }
			},
			rem_comment: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-reminder-header-comment' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' }
			},
			rem_is_repeating: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-reminder-header-is-repeating' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'boolean' },
				valueParser: ( value, row ) => {
					return new OO.ui.HtmlSnippet(
						value ?
							`${mw.message( 'bs-reminder-date-repeat-ends-on-label' )} ${row.rem_repeat_date_end}` :
							mw.message( 'bs-reminder-no' ).plain()
					);
				}
			},
			edit: {
				headerText: mw.message( 'bs-reminder-header-action-edit' ).text(),
				title: mw.message( 'bs-reminder-title-edit' ).text(),
				type: 'action',
				actionId: 'edit',
				icon: 'edit',
				invisibleHeader: true,
				visibleOnHover: true,
				width: 30
			},
			delete: {
				headerText: mw.message( 'bs-reminder-header-action-delete' ).text(),
				title: mw.message( 'bs-reminder-title-delete' ).text(),
				type: 'action',
				actionId: 'delete',
				icon: 'trash',
				invisibleHeader: true,
				visibleOnHover: true,
				width: 30
			}
		},
		store: this.store,
		provideExportData: () => {
			const deferred = $.Deferred();

			( async () => {
				try {
					this.store.setPageSize( 99999 );
					const response = await this.store.reload();
					const $table = $( '<table>' );

					const $thead = $( '<thead>' )
						.append( $( '<tr>' )
							.append( $( '<th>' ).text( mw.message( 'bs-reminder-header-username' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-reminder-header-pagename' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-reminder-header-type' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-reminder-header-date' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-reminder-header-comment' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-reminder-header-is-repeating' ).text() ) )
						);

					const $tbody = $( '<tbody>' );
					for ( const id in response ) {
						if ( response.hasOwnProperty( id ) ) { // eslint-disable-line no-prototype-builtins
							const record = response[ id ];
							const type = record.rem_type || 'page';
							const messageType = mw.config.get( 'bsgReminderRegisteredTypes' )[ type ].LabelMsg;
							const formattedDate = record.rem_repeat_date_end.replace( ',', ' -' ); // CSV comma delimiter
							const messageRepeating = record.rem_is_repeating == 1 ? // eslint-disable-line eqeqeq
								`${mw.message( 'bs-reminder-date-repeat-ends-on-label' ).plain()} ${formattedDate}` :
								mw.message( 'bs-reminder-no' ).plain();

							$tbody.append( $( '<tr>' )
								.append( $( '<td>' ).text( record.user_name ) )
								.append( $( '<td>' ).text( record.page_title ) )
								.append( $( '<td>' ).text( messageType ) )
								.append( $( '<td>' ).text( record.reminder_date ) )
								.append( $( '<td>' ).text( record.rem_comment ) )
								.append( $( '<td>' ).text( messageRepeating ) )
							);
						}
					}

					$table.append( $thead, $tbody );

					deferred.resolve( `<table>${$table.html()}</table>` );
				} catch ( error ) {
					deferred.reject( 'Failed to load data' );
				}
			} )();

			return deferred.promise();
		}
	};

	return gridCfg;
};

ext.bluespice.reminder.ui.panel.SpecialReminderPanel.prototype.getToolbarActions = function () {
	return [
		this.getAddAction( {
			icon: 'add',
			title: mw.message( 'bs-reminder-create-reminder-label' ).plain(),
			displayBothIconAndLabel: true
		} )
	];
};

ext.bluespice.reminder.ui.panel.SpecialReminderPanel.prototype.onAction = function ( action, row ) {
	if ( action === 'add' ) {
		this.showReminderDialog( {
			user: mw.config.get( 'wgUserName' ),
			date: new Date( mw.config.get( 'DefaultReminderPeriod' ) * 1000 )
		} );
	}
	if ( action === 'edit' ) {
		const dialogData = {
			id: row.id,
			user: row.user_name,
			page: row.page_title,
			date: row.reminder_date,
			comment: row.rem_comment,
			isRepeating: !!parseInt( row.rem_is_repeating )
		};

		if ( dialogData.isRepeating ) {
			try {
				dialogData.repeatConfig = JSON.parse( row.rem_repeat_config );
			} catch ( e ) {}
		}

		this.showReminderDialog( dialogData );
	}
	if ( action === 'delete' ) {
		bs.util.confirm(
			'REremove',
			{
				title: mw.message( 'bs-reminder-title-delete' ).plain(),
				text: mw.message( 'bs-reminder-text-delete', 1 ).text()
			},
			{
				ok: () => { this.onRemoveReminderOk( row.id ); }
			}
		);
	}
};

ext.bluespice.reminder.ui.panel.SpecialReminderPanel.prototype.onRemoveReminderOk = async function ( id ) {
	await bs.api.tasks.execSilent(
		'reminder',
		'deleteReminder',
		{
			reminderId: id
		}
	);

	this.store.reload();
};

ext.bluespice.reminder.ui.panel.SpecialReminderPanel.prototype.showReminderDialog = async function ( data = {} ) {
	const config = {
		data: data,
		canCreateForOthers: !this.username,
		skipActionDefinitions: true
	};

	const reminderPage = new bs.reminder.ui.ReminderPage( 'add-reminder', config );

	const dialogAdd = new OOJSPlus.ui.dialog.BookletDialog( {
		id: 'bs-reminder-dialog-create',
		pages: [ reminderPage ]
	} );

	const result = await dialogAdd.show().closed;
	if ( result.success ) {
		this.store.reload();
	}
};
