define(['Ajax', 'Ui/Dialog', 'Language'], function(Ajax, UiDialog, Language) {
	"use strict";

	function SnippetDetailDialog(button, callback) { this.init(button, callback); }

	SnippetDetailDialog.prototype = {
		init: function (button) {
			var elements = elBySelAll(button);
			for (var i = 0, length = elements.length; i < length; i++) {
				elements[i].addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
			}

		},

		_click: function(event) {
			Ajax.api(this, {
				actionName: 'getDetailDialog',
				objectIDs: [ event.target.dataset.objectId ]
			});
		},

		/**
		 * Sets up ajax request object.
		 *
		 * @return	{object}	request options
		 */
		_ajaxSetup: function() {
			return {
				data: {
					className: 'wcf\\data\\htaccess\\content\\HtaccessContentAction'
				}
			};
		},

		/**
		 * Handles a successful request.
		 *
		 * @param	{object}	data	reponse data
		 */
		_ajaxSuccess: function(data) {
			this.template = data.returnValues;

			var dialog = UiDialog.getDialog(this);
			if (dialog === undefined) {
				UiDialog.open(this)
			} else {
				UiDialog._updateDialog('htaccessContentDetailDialog', data.returnValues);
			}
		},

		/**
		 * Returns the data to set up the issue create dialog.
		 *
		 * @return	{object}
		 */
		_dialogSetup: function() {
			return {
				id: 'htaccessContentDetailDialog',
				options: {
					onShow: this._showDialog.bind(this),
					title: Language.get('wcf.acp.htaccess.content.info')
				},
				source: this.template
			};
		},

		/**
		 * Initializes the dialog.
		 */
		_showDialog: function() {
			UiDialog.getDialog(this).dialog;
		}
	};

	return SnippetDetailDialog;
});
