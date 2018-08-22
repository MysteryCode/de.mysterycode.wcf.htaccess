<?php

namespace wcf\data\htaccess;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\package\PackageCache;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * @property HtaccessEditor[] $objects
 * @method HtaccessEditor getSingleObject()
 */
class HtaccessAction extends AbstractDatabaseObjectAction {
	public function validateGenerateMissingSecuringFiles() {
		$this->readObjects();
	}
	
	public function generateMissingSecuringFiles() {
		$files = [];
		
		foreach ($this->objects as $object) {
			FileUtil::makePath(dirname($object->getPath()));
			
			file_put_contents($object->getPath(), $object->defaultContent);
			FileUtil::makeWritable($object->getPath());
			
			$statement = WCF::getDB()->prepareStatement("INSERT IGNORE INTO wcf" . WCF_N . "_package_installation_file_log (packageID, filename, application) VALUES (?, ?, ?)");
			$statement->execute([
				PackageCache::getInstance()->getPackageID($object->package),
				str_replace('//', '/', $object->path . '/.htaccess'),
				$object->application
			]);
			
			$files[] = str_replace('//', '/', $object->path . '/.htaccess');
		}
		
		return $files;
	}
}
