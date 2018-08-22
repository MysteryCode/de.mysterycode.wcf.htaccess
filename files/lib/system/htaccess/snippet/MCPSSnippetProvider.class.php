<?php

namespace wcf\system\htaccess\snippet;

use mcps\util\RewriteUtil;
use wcf\data\htaccess\content\HtaccessContent;

class MCPSSnippetProvider implements ISnippetProvider {
	/**
	 * @inheritDoc
	 */
	public function __construct(HtaccessContent $content = null) {
		// bypass
	}
	
	/**
	 * @inheritDoc
	 */
	public function generateOutput() {
		$ru = new RewriteUtil('apache');
		return $ru->getOutput();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getForm() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public static function isUseable() {
		return true;
	}
}
