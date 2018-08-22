<?php

namespace wcf\data\htaccess;

use wcf\data\DatabaseObject;
use wcf\data\htaccess\content\HtaccessContentList;
use wcf\system\application\ApplicationHandler;
use wcf\util\FileUtil;

/**
 * @property-read integer $fileID
 * @property-read string  $package
 * @property-read boolean $isSecuring
 * @property-read string  $defaultContent
 * @property-read string  $path
 * @property-read string  $application
 */
class Htaccess extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'fileID';
	
	/**
	 * @return string
	 */
	public function getPath() {
		return str_replace('//', '/', FileUtil::getRealPath(constant(strtoupper($this->application) . '_DIR')) . $this->path . '/.htaccess');
	}
	
	/**
	 * @return string
	 */
	public function getShortPath() {
		return str_replace('//', '/', $this->application . '/' . $this->path . '/.htaccess');
	}
	
	/**
	 * @return boolean
	 */
	public static function generateFiles() {
		$objectList = new HtaccessContentList();
		$objectList->sqlOrderBy .= "htaccess_content.showOrder ASC";
		$objectList->sqlSelects .= "ht.application, ht.path";
		$objectList->sqlJoins .= " LEFT JOIN wcf" . WCF_N . "_htaccess ht ON ht.fileID = htaccess_content.fileID";
		$objectList->getConditionBuilder()->add('htaccess_content.isDisabled <> 1');
		$objectList->getConditionBuilder()->add('htaccess_content.fileID IN (SELECT hc.fileID FROM wcf' . WCF_N . '_htaccess hc WHERE hc.application IN (?)) OR (htaccess_content.isUnique = 1 AND htaccess_content.fileID IS NULL)', [ApplicationHandler::getInstance()->getAbbreviations()]);
		$objectList->readObjects();
		
		$t = $objectTree = [];
		/** @var false|\wcf\data\application\Application $a */
		$a = false;
		foreach (ApplicationHandler::getInstance()->getApplications() as $app) {
			$t[$app->domainName][$app->domainPath] = $app;
		}
		if (count($t) === 1) {
			$b = array_shift($t);
			$a = array_shift($b);
		}
		
		foreach ($objectList->getObjects() as $object) {
			if ($object->forceSingleFile || $a === false) {
				if ($object->isUseable()) $objectTree[$object->getFile()->getPath()][] = $object->getOutput();
			}
			else if ($a !== false) {
				if (
					(
						($object->isUnique && $object->isGlobal && $object->fileID == null) ||
						(!$object->isUnique && $object->isGlobal && $object->fileID != null) ||
						(!$object->isUnique && !$object->isGlobal)
					) && $object->isUseable()
				) {
					$objectTree[$a->getPackage()->getAbsolutePackageDir() . '.htaccess'][] = $object->getOutput();
				}
			}
		}
		
		$success = true;
		foreach ($objectTree as $path => $snippetList) {
			$content = implode("\n\n", $snippetList);
			$success = $success && file_put_contents($path, $content) !== false;
		}
		
		return $success;
	}
}
