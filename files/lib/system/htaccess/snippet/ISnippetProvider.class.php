<?php

namespace wcf\system\htaccess\snippet;

use wcf\data\htaccess\content\HtaccessContent;

interface ISnippetProvider {
	/**
	 * ISnippetProvider constructor.
	 *
	 * @param \wcf\data\htaccess\content\HtaccessContent $content
	 */
	public function __construct(HtaccessContent $content = null);
	
	/**
	 * Returns the compiled formular for additional configuration on
	 * the content add/edit form.
	 *
	 * @return string
	 */
	public function getForm();
	
	/**
	 * Returns the compiled output ready for the .htacess-file
	 *
	 * @return string
	 */
	public function generateOutput();
	
	/**
	 * Returns true if the provider can be used and returns a
	 * string containing an error message otherwise
	 *
	 * @return string|true
	 */
	public static function isUseable();
}
