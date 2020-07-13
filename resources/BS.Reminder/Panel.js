/**
 * Reminder Panel
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage Reminder
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.define( 'BS.Reminder.Panel', {
	extend: 'BS.CRUDGridPanel',
	requires: [ 'BS.store.BSApi', 'BS.Reminder.PanelDialog' ],
	initComponent: function() {
		this.strMain = new BS.store.BSApi({
			apiAction: 'bs-reminder-store',
			autoLoad: true,
			remoteSort: true,
			fields: [ 'id', 'user_name', 'page_title', 'page_link', 'reminder_date', 'article_id', 'rem_comment', 'rem_is_repeating' ],
			proxy: {
				extraParams: {
					query: mw.config.get( 'BSReminderUsername', false )
				}
			}
		} );
		if ( mw.config.get( 'BSReminderShowUserColumn', false ) || !mw.config.get( 'BSReminderUsername', false ) ) {
			this.colUserName = Ext.create( 'Ext.grid.column.Template', {
				id: 'user_name',
				header: mw.message( 'bs-reminder-header-username' ).plain(),
				sortable: true,
				dataIndex: 'user_name',
				tpl: '<a href="{user_page}">{user_name}</a>',
				filter: {
					type: 'string'
				}
			} );
			this.colMainConf.columns.push( this.colUserName );
		}

		this.colPageTitle = Ext.create( 'Ext.grid.column.Template', {
			id: 'page_title',
			header: mw.message('bs-reminder-header-pagename').plain(),
			sortable: true,
			dataIndex: 'page_title',
			tpl: '<a href="{page_link}">{page_title}</a>',
			filter: {
				type: 'string'
			}
		} );
		var filter = [];
		for( var idx in mw.config.get( 'bsgReminderRegisteredTypes' ) ) {
			if ( idx === '' ) {
				continue;
			}
			filter.push( idx );
		}

		this.colReminderType = Ext.create( 'Ext.grid.column.Column', {
			id: 'reminder_type',
			header: mw.message( 'bs-reminder-header-type' ).plain(),
			sortable: true,
			dataIndex: 'rem_type',
			renderer: function( val ) {
				if ( val === '' ) {
					val = 'page';
				}
				return mw.config.get( 'bsgReminderRegisteredTypes' )[val].LabelMsg;
			},
			filter: {
				type: 'list',
				options: filter,
				value: 'page'
			}
		});
		this.colReminderDate = Ext.create( 'Ext.grid.column.Column', {
			id: 'reminder_date',
			header: mw.message('bs-reminder-header-date').plain(),
			sortable: true,
			dataIndex: 'reminder_date',
			renderer: this.renderDate,
			filter: {
				type: 'date'
			},
			filterable: true
		} );

		this.colComment = Ext.create( 'Ext.grid.column.Column', {
			id: 'reminder_comment',
			header: mw.message('bs-reminder-header-comment').plain(),
			sortable: false,
			dataIndex: 'rem_comment',
			filter: {
				type: 'string'
			}
		});

		this.colComment = Ext.create( 'Ext.grid.column.Column', {
			id: 'reminder_is_repeating',
			header: mw.message('bs-reminder-header-is-repeating').plain(),
			sortable: false,
			dataIndex: 'rem_is_repeating',
			filter: {
				type: 'string'
			},
			renderer: function(value, metaData, record, rowIndex, colIndex, store) {
				if ( parseInt( value ) === 1) {
					return mw.message('bs-reminder-date-repeat-ends-on-label') +
						' ' + record.get( 'rem_repeat_date_end' );
				} else {
					return mw.message('bs-reminder-no').plain();
				}
			}
		});

		this.colMainConf.columns.push( this.colPageTitle );
		this.colMainConf.columns.push( this.colReminderType );
		this.colMainConf.columns.push( this.colReminderDate );
		this.colMainConf.columns.push( this.colComment );

		this.callParent( arguments );
	},
	makeSelModel: function(){
		this.smModel = Ext.create( 'Ext.selection.CheckboxModel', {
			mode: "MULTI",
			selType: 'checkboxmodel'
		});
		return this.smModel;
	},
	renderDate: function( value ) {
		var remind = new Date( value );
		var today = new Date();
		if ( today > remind ) {
			return "<span style='color:red'>" + value + "</span>";
		};
		return value;
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		bs.util.confirm(
			'REremove',
			{
				text: mw.message(
					'bs-reminder-text-delete',
					this.grdMain.getSelectionModel().getSelection().length
				).text(),
				title: mw.message( 'bs-reminder-title-delete' ).plain()
			},
			{
				ok: this.onRemoveReminderOk,
				cancel: function() {},
				scope: this
			}
		);
	},
	onBtnEditClick: function () {
		this.selectedRow = this.grdMain.getSelectionModel().getSelection();
		var rowData = this.selectedRow[0].getData();
		var obj = {
			id: rowData.id,
			date: rowData.reminder_date,
			page_title: rowData.page_title,
			userName: rowData.user_name,
			comment: rowData.rem_comment,
			calledFromArticle: false
		};
		var me = this;
		if ( !this.dlgEdit ) {
			this.dlgEdit = new BS.Reminder.PanelDialog({
				id: 'bs-reminder-dlg-edit'
			});
			this.dlgEdit.on( 'ok', me.onEditReminderOk, me );
		}
		this.dlgEdit.setData( obj );
		this.dlgEdit.show();
	},
	onBtnAddClick: function () {
		var obj = {
			date: new Date( mw.config.get( 'DefaultReminderPeriod' ) * 1000 ),
			userName: mw.user.getName()
		}
		var me = this;
		if ( !this.dlgAdd ) {
			this.dlgAdd = new BS.Reminder.PanelDialog({
				id: 'bs-reminder-dlg-add'
			});
			this.dlgAdd.on( 'ok', me.onAddReminderOk, me );
		}
		this.dlgAdd.setData( obj );
		this.dlgAdd.show();
	},
	onAddReminderOk: function( data ) {
		var obj = this.dlgAdd.getData();
		var me = this;
		bs.api.tasks.exec(
			'reminder',
			'saveReminder',
			obj
			)
			.done(function(){
				me.reloadStore();
			});
	},
	onEditReminderOk: function( data ) {
		var obj = this.dlgEdit.getData();
		var me = this;
		bs.api.tasks.exec(
			'reminder',
			'saveReminder',
			obj
		)
		.done(function(){
			me.reloadStore();
		});
	},
	onRemoveReminderOk: function() {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		for (var i = 0; i < selectedRow.length; i++){
			var Id = selectedRow[i].get( 'id' );
			var me = this;
			bs.api.tasks.exec(
				'reminder',
				'deleteReminder',
				{
					'reminderId': Id
				}
			)
			.done(function( response, xhr ){
				me.reloadStore();
			});
		}
	},
	reloadStore: function() {
		this.strMain.reload();
	},
	makeRowActions: function() {
		var actions = this.callParent( arguments );
		for( var i = 0; i < actions.length; i++ ) {
			actions[i].isDisabled = function( view, rowIndex, colIndex, item, record  ) {
				return record.get( 'rem_type' ) !== '' && !record.get( 'rem_type' ) !== 'page' ;
			};
		}

		return actions;
	}
});
