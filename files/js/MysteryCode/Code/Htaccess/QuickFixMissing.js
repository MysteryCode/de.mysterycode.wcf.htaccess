define(['Ajax'], function(Ajax) {
	"use strict";

	function QuickFixMissing(button, callback) { this.init(button, callback); }

	QuickFixMissing.prototype = {
		button: null,
		fileIDs: [],

		init: function (button, fileIDs) {
			this.button = elBySel(button);
			this.fileIDs = fileIDs;

			this.button.addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
		},

		_click: function() {
			Ajax.api(this, {
				actionName: 'generateMissingSecuringFiles',
				objectIDs: this.fileIDs
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
					className: 'wcf\\data\\htaccess\\HtaccessAction'
				}
			};
		},

		/**
		 * Handles a successful request.
		 *
		 * @param	{object}	data	reponse data
		 */
		_ajaxSuccess: function(data) {
			window.location.reload();
		}
	};

	return QuickFixMissing;
});
