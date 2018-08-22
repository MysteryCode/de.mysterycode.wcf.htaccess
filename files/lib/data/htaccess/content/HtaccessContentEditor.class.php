<?php

namespace wcf\data\htaccess\content;

use wcf\data\DatabaseObjectEditor;
use wcf\data\htaccess\Htaccess;
use wcf\data\IEditableCachedObject;

/**
 * @method   HtaccessContent       getDecoratedObject()
 * @property HtaccessContentEditor $object
 *
 * @mixin    HtaccessContent
 */
class HtaccessContentEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = HtaccessContent::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		Htaccess::generateFiles();
	}
}
