<?php

namespace wcf\data\htaccess;

use wcf\data\DatabaseObjectEditor;

/**
 * @property HtaccessEditor $object
 * @method Htaccess getDecoratedObject()
 * @mixin Htaccess
 */
class HtaccessEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Htaccess::class;
}
