<?php

namespace wcf\data\htaccess;

use wcf\data\DatabaseObject;
use wcf\data\htaccess\content\RecursiveHtaccessContentList;
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
		$contentList = new RecursiveHtaccessContentList();
		$contentList->readObjects();
		$objectTree = $contentList->getOutputTree();
		
		$success = true;
		foreach ($objectTree as $path => $snippetList) {
			$content = explode("\n", implode(" \n \n",  $snippetList));
			$success = $success && file_put_contents($path, $content) !== false;
		}
		
		return $success;
	}
}
