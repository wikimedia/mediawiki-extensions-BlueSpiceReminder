ext.bluespice = ext.bluespice || {};
ext.bluespice.reminder = ext.bluespice.reminder || {};
ext.bluespice.reminder.ui = ext.bluespice.reminder.ui || {};
ext.bluespice.reminder.ui.panel = ext.bluespice.reminder.ui.panel || {};

ext.bluespice.reminder.ui.panel.SpecialReminderPanel = function ( cfg ) {
	ext.bluespice.reminder.ui.panel.SpecialReminderPanel.super.apply( this, cfg );
	this.$element = $( '<div>' );

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

	this.setup();
};

OO.inheritClass( ext.bluespice.reminder.ui.panel.SpecialReminderPanel, OO.ui.PanelLayout );

ext.bluespice.reminder.ui.panel.SpecialReminderPanel.prototype.setup = function () {
	const addReminderButton = new OO.ui.ButtonWidget( {
		icon: 'add',
		title: mw.message( 'bs-reminder-title-add' ).plain(),
		framed: false
	} );
	addReminderButton.connect( this, {
		click: () => this.showReminderDialog( {
			user: mw.config.get( 'wgUserName' ),
			date: new Date( mw.config.get( 'DefaultReminderPeriod' ) * 1000 )
		} )
	} );

	this.tools = [ addReminderButton ];

	const gridCfg = this.setupGridConfig();
	this.grid = new OOJSPlus.ui.data.GridWidget( gridCfg );
	this.grid.connect( this, {
		action: 'doActionOnRow'
	} );

	this.$element.append( this.grid.$element );
};

ext.bluespice.reminder.ui.panel.SpecialReminderPanel.prototype.setupGridConfig = function () {
	const gridCfg = {
		exportable: true,
		style: 'differentiate-rows',
		columns: {
			user_name: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-reminder-header-username' ).plain(),
				type: 'user',
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
				icon: 'settings',
				invisibleHeader: true,
				width: 30
			},
			delete: {
				headerText: mw.message( 'bs-reminder-header-action-delete' ).text(),
				title: mw.message( 'bs-reminder-title-delete' ).text(),
				type: 'action',
				actionId: 'delete',
				icon: 'trash',
				invisibleHeader: true,
				width: 30
			}
		},
		store: this.store,
		tools: this.tools,
		provideExportData: () => {
			const deferred = $.Deferred();

			( async () => {
				try {
					this.store.setPageSize( 99999 );
					const response = await this.store.reload();

					const $table = $( '<table>' );
					let $row = $( '<tr>' );

					$row.append( $( '<td>' ).text( mw.message( 'bs-reminder-header-username' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-reminder-header-pagename' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-reminder-header-type' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-reminder-header-date' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-reminder-header-comment' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-reminder-header-is-repeating' ).text() ) );

					$table.append( $row );

					for ( const id in response ) {
						if ( response.hasOwnProperty( id ) ) { // eslint-disable-line no-prototype-builtins
							const record = response[ id ];
							$row = $( '<tr>' );

							$row.append( $( '<td>' ).text( record.user_name ) );
							$row.append( $( '<td>' ).text( record.page_title ) );

							const type = record.rem_type || 'page';
							$row.append( $( '<td>' ).text( mw.config.get( 'bsgReminderRegisteredTypes' )[ type ].LabelMsg ) );

							$row.append( $( '<td>' ).text( record.reminder_date ) );
							$row.append( $( '<td>' ).text( record.rem_comment ) );

							const formattedDate = record.rem_repeat_date_end.replace( ',', ' -' ); // CSV comma delimiter
							$row.append( $( '<td>' ).text(
								record.rem_is_repeating == 1 ? // eslint-disable-line eqeqeq
									`${mw.message( 'bs-reminder-date-repeat-ends-on-label' ).plain()} ${formattedDate}` :
									mw.message( 'bs-reminder-no' ).plain()
							) );

							$table.append( $row );
						}
					}

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

ext.bluespice.reminder.ui.panel.SpecialReminderPanel.prototype.doActionOnRow = function ( action, row ) {
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
	await bs.api.tasks.exec(
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
