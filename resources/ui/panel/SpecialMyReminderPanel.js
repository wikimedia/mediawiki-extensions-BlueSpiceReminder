ext.bluespice = ext.bluespice || {};
ext.bluespice.reminder = ext.bluespice.reminder || {};
ext.bluespice.reminder.ui = ext.bluespice.reminder.ui || {};
ext.bluespice.reminder.ui.panel = ext.bluespice.reminder.ui.panel || {};

ext.bluespice.reminder.ui.panel.SpecialMyReminderPanel = function ( cfg ) {
	ext.bluespice.reminder.ui.panel.SpecialMyReminderPanel.super.apply( this, cfg );
	this.$element = $( '<div>' );

	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-reminder-store',
		pageSize: 25
	} );

	this.store.filter( new OOJSPlus.ui.data.filter.String( {
		value: mw.config.get( 'wgUserName' ),
		operator: 'eq',
		type: 'string'
	} ), 'user_name' );

	this.setup();
};

OO.inheritClass( ext.bluespice.reminder.ui.panel.SpecialMyReminderPanel, OO.ui.PanelLayout );

ext.bluespice.reminder.ui.panel.SpecialMyReminderPanel.prototype.setup = function () {
	const addReminderButton = new OO.ui.ButtonWidget( {
		icon: 'add',
		title: mw.message( 'bs-reminder-title-add' ).plain(),
		framed: false
	} );
	addReminderButton.connect( this, {
		click: 'showReminderDialog'
	} );

	this.tools = [ addReminderButton ];

	const gridCfg = this.setupGridConfig();
	this.grid = new OOJSPlus.ui.data.GridWidget( gridCfg );
	this.grid.connect( this, {
		action: 'doActionOnRow'
	} );

	this.$element.append( this.grid.$element );
};

ext.bluespice.reminder.ui.panel.SpecialMyReminderPanel.prototype.setupGridConfig = function () {
	const gridCfg = {
		exportable: true,
		style: 'differentiate-rows',
		columns: {
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
						value ? // eslint-disable-line eqeqeq
							`${mw.message( 'bs-reminder-date-repeat-ends-on-label' )} ${row.rem_repeat_date_end}` :
							mw.message( 'bs-reminder-no' ).plain()
					);
				}
			},
			edit: {
				type: 'action',
				title: mw.message( 'bs-reminder-title-edit' ).text(),
				actionId: 'edit',
				icon: 'settings',
				headerText: mw.message( 'bs-reminder-header-action-edit' ).text(),
				invisibleHeader: true
			},
			delete: {
				type: 'action',
				title: mw.message( 'bs-reminder-title-delete' ).text(),
				actionId: 'delete',
				icon: 'trash',
				headerText: mw.message( 'bs-reminder-header-action-delete' ).text(),
				invisibleHeader: true
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

					$row.append( $( '<td>' ).text( mw.message( 'bs-reminder-header-pagename' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-reminder-header-type' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-reminder-header-date' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-reminder-header-comment' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-reminder-header-is-repeating' ).text() ) );

					$table.append( $row );

					for ( const id in response ) {
						if ( response.hasOwnProperty( id ) ) { // eslint-disable-line no-prototype-builtins, max-len
							const record = response[ id ];
							$row = $( '<tr>' );

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

ext.bluespice.reminder.ui.panel.SpecialMyReminderPanel.prototype.doActionOnRow = function ( action, row ) { // eslint-disable-line max-len
	if ( action === 'edit' ) {
		const dialogData = {
			id: row.id,
			date: row.reminder_date,
			page: row.page_title,
			user: row.user_name,
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
				text: mw.message( 'bs-reminder-text-delete', 1 ).text(),
				title: mw.message( 'bs-reminder-title-delete' ).plain()
			},
			{
				ok: () => { this.onRemoveReminderOk( row.id ); }
			}
		);
	}
};

ext.bluespice.reminder.ui.panel.SpecialMyReminderPanel.prototype.onRemoveReminderOk = async function ( id ) { // eslint-disable-line max-len
	await bs.api.tasks.exec(
		'reminder',
		'deleteReminder',
		{
			reminderId: id
		}
	);

	this.store.reload();
};

ext.bluespice.reminder.ui.panel.SpecialMyReminderPanel.prototype.showReminderDialog = async function ( data = {} ) { // eslint-disable-line max-len
	data.user = mw.config.get( 'wgUserName' );
	data.date = new Date( mw.config.get( 'DefaultReminderPeriod' ) * 1000 );

	const config = {
		data: data,
		canCreateForOthers: false,
		skipActionDefinitions: true
	};

	const reminderPage = data && data.page ?
		new bs.reminder.ui.CreateReminderForPage( config ) :
		new bs.reminder.ui.ReminderPage( 'add-reminder', config );

	const dialogAdd = new OOJSPlus.ui.dialog.BookletDialog( {
		id: 'bs-reminder-dialog-create',
		pages: [ reminderPage ]
	} );

	const result = await dialogAdd.show().closed;
	if ( result.success ) {
		this.store.reload();
	}
};
