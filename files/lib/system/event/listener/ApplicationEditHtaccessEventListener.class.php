<?php

namespace wcf\system\event\listener;

use wcf\acp\form\ApplicationEditForm;
use wcf\data\htaccess\content\HtaccessContent;
use wcf\data\htaccess\content\HtaccessContentEditor;
use wcf\data\htaccess\content\HtaccessContentList;
use wcf\data\htaccess\Htaccess;
use wcf\data\package\Package;
use wcf\system\exception\SystemException;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;

class ApplicationEditHtaccessEventListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if ($eventObj instanceof ApplicationEditForm) {
			/** @var ApplicationEditForm $eventObj */
			
			$contentList = new HtaccessContentList();
			$contentList->getConditionBuilder()->add('isDisabled <> 1');
			$contentList->getConditionBuilder()->add('application = ?', [$eventObj->application->getAbbreviation()]);
			$contentList->readObjectIDs();
			if (!empty($contentList->getObjectIDs())) {
				Htaccess::generateFiles();
			}
		}
		else if($eventObj instanceof PackageInstallationDispatcher) {
			/** @var PackageInstallationDispatcher $eventObj */
			
			if ($eventObj->getPackage()->isApplication) {
				$statement = WCF::getDB()->prepareStatement("SELECT DISTINCT	htaccess_content.contentIdentifier, htaccess_content.*, htc.fileID as referenceFileID
					FROM		wcf".WCF_N."_htaccess_content htaccess_content
					LEFT JOIN	wcf".WCF_N."_htaccess_content htc
					ON		htc.contentIdentifier = htaccess_content.contentIdentifier
					AND		htc.contentID  = (
								SELECT	MIN(htcc.contentID)
								FROM	wcf".WCF_N."_htaccess_content htcc
								WHERE	htcc.fileID IS NOT NULL
									AND htcc.contentIdentifier = htaccess_content.contentIdentifier
					)
					WHERE		htaccess_content.isGlobal = 1
							AND htaccess_content.fileID IS NULL");
				$statement->execute();
				while ($row = $statement->fetchObject(HtaccessContent::class)) {
					/** @noinspection PhpUndefinedFieldInspection */
					$contentR = new Htaccess($row->referenceFileID);
					
					/** @noinspection PhpDeprecationInspection */
					$data = $row->getData();
					unset($data['contentID']);
					if (isset($data['referenceFileID'])) unset($data['referenceFileID']);
					$data['fileID'] = $this->getFile($eventObj->getPackage()->package, Package::getAbbreviation($eventObj->getPackage()->package), $contentR->path);
					$data['isDisabled'] = 1;
					$data['additionalData'] = serialize([]);
					
					HtaccessContentEditor::create($data);
				}
			}
		}
	}
	
	/**
	 * Returns the id of the file the content should match
	 *
	 * @param string $package
	 * @param string $application
	 * @param string $path
	 * @return integer
	 * @throws \wcf\system\exception\SystemException
	 */
	protected function getFile($package, $application, $path) {
		$sql = "SELECT	fileID
				FROM	wcf".WCF_N."_htaccess
				WHERE	application = ?
					AND path = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute([$application, strtolower($path) == 'null' ? NULL : $path]);
		$row = $statement->fetchSingleRow();
		if ($row !== false) {
			return $row['fileID'];
		} else {
			if (empty($data['package']) === null) {
				throw new SystemException("Parameter 'package' is missing or incorrect.");
			}
			
			$statement = WCF::getDB()->prepareStatement("INSERT INTO wcf" . WCF_N . "_htaccess (package, path, application) VALUES (?,?,?)");
			$statement->execute([$package, $path, $application]);
			return WCF::getDB()->getInsertID('wcf' . WCF_N . '_htaccess', 'fileID');
			//TODO check
		}
	}
}
