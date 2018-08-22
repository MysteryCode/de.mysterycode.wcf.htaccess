<?php

namespace wcf\data\htaccess\content;

use wcf\system\application\ApplicationHandler;

/**
 * @method HtaccessContent[] getObjects()
 * @property HtaccessContent[] $objects
 */
class RecursiveHtaccessContentList extends HtaccessContentList {
	/**
	 * @inheritDoc
	 */
	public $className = HtaccessContent::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'htaccess_content.showOrder ASC';
	
	/**
	 * @inheritDoc
	 */
	public $sqlSelects = 'ht.application, ht.path';
	
	/**
	 * @inheritDoc
	 */
	public $sqlJoins = ' LEFT JOIN wcf' . WCF_N . '_htaccess ht ON ht.fileID = htaccess_content.fileID';
	
	/**
	 * @var HtaccessContent[][]
	 */
	protected $objectTree = [];
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();
		
		$this->conditionBuilder->add('htaccess_content.isDisabled <> 1');
		$this->conditionBuilder->add('htaccess_content.fileID IN (SELECT hc.fileID FROM wcf' . WCF_N . '_htaccess hc WHERE hc.application IN (?)) OR (htaccess_content.isUnique = 1 AND htaccess_content.fileID IS NULL)', [ApplicationHandler::getInstance()->getAbbreviations()]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		$ret = parent::readObjects();
		
		$t = [];
		/** @var false|\wcf\data\application\Application $a */
		$a = false;
		foreach (ApplicationHandler::getInstance()->getApplications() as $app) {
			$t[$app->domainName][$app->domainPath] = $app;
		}
		if (count($t) === 1) {
			$b = array_shift($t);
			$a = array_shift($b);
		}
		
		foreach ($this->objects as $object) {
			if (($object->forceSingleFile || $a === false) && $object->isUseable()) {
				$this->objectTree[$object->getFile()->getPath()][] = $object;
			}
			else if ($a !== false) {
				if (
					(
						($object->isUnique && $object->isGlobal && $object->fileID == null) ||
						(!$object->isUnique && $object->isGlobal && $object->fileID != null) ||
						(!$object->isUnique && !$object->isGlobal)
					) && $object->isUseable()
				) {
					$this->objectTree[$a->getPackage()->getAbsolutePackageDir() . '.htaccess'][] = $object;
				}
			}
		}
		
		return $ret;
	}
	
	public function getOutputTree() {
		$outputTree = [];
		
		foreach ($this->objectTree as $path => $contentList) {
			$p = null;
			
			foreach ($contentList as $showOrder => $content) {
				$n = isset($this->objectTree[$path][$showOrder + 1]) ? $this->objectTree[$path][$showOrder + 1] : null;
				$outputTree[$path][$showOrder] = $content->getOutput($p, $n);
				$p = $content;
			}
		}
		
		return $outputTree;
	}
}
