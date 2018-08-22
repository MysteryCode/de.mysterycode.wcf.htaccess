<?php

namespace wcf\acp\page;

use wcf\data\htaccess\content\HtaccessContentList;
use wcf\data\htaccess\Htaccess;
use wcf\data\htaccess\HtaccessList;
use wcf\page\MultipleLinkPage;
use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;

/**
 * @property \wcf\data\htaccess\content\HtaccessContentList $objectList
 */
class HtaccessContentListPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.htaccess.content';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.htaccess.canView'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = HtaccessContentList::class;
	
	/**
	 * @inheritDoc
	 */
	public $sortField = 'htaccess_content.showOrder';
	
	/**
	 * @inheritDoc
	 */
	public $sortOrder = 'ASC';
	
	/**
	 * @var \wcf\data\htaccess\Htaccess[]
	 */
	public $availableFiles;
	
	/**
	 * @var integer
	 */
	public $fileID;
	
	/**
	 * @var \wcf\data\htaccess\Htaccess
	 */
	public $file;
	
	/**
	 * @var Htaccess[][]
	 */
	public $objectTree = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['id'])) {
			$this->fileID = intval($_REQUEST['id']);
			$this->file = new Htaccess($this->fileID);
		} else {
			$this->itemsPerPage = 999999;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects .= "ht.application, ht.path";
		$this->objectList->sqlJoins .= " LEFT JOIN wcf" . WCF_N . "_htaccess ht ON ht.fileID = htaccess_content.fileID";
		//$this->objectList->getConditionBuilder()->add('(htaccess_content.fileID IS NOT NULL OR htaccess_content.isUnique = 1)');
		//$this->objectList->getConditionBuilder()->add('htaccess_content.fileID IN (SELECT hc.fileID FROM wcf' . WCF_N . '_htaccess hc WHERE hc.application IN (?))', [ApplicationHandler::getInstance()->getAbbreviations()]);
		$this->objectList->getConditionBuilder()->add('htaccess_content.fileID IN (SELECT hc.fileID FROM wcf' . WCF_N . '_htaccess hc WHERE hc.application IN (?)) OR (htaccess_content.isUnique = 1 AND htaccess_content.fileID IS NULL)', [ApplicationHandler::getInstance()->getAbbreviations()]);
		if (!empty($this->fileID)) $this->objectList->getConditionBuilder()->add('htaccess_content.fileID = ?', [$this->fileID]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
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
		
		foreach ($this->objectList->getObjects() as $object) {
			if ($object->forceSingleFile || $a === false) $this->objectTree[$object->application . '/' . $object->path . (empty($object->path) ? '.htaccess' : '/.htaccess')][] = $object;
			else {
				if ($object->isUnique && $object->isGlobal && $object->fileID == null) $this->objectTree[$a->getAbbreviation() . '/.htaccess'][] = $object;
				else if (!$object->isUnique && $object->isGlobal && $object->fileID != null) $this->objectTree[$a->getAbbreviation() . '/.htaccess'][] = $object;
				else if (!$object->isUnique && !$object->isGlobal) $this->objectTree[$a->getAbbreviation() . '/.htaccess'][] = $object;
			}
		}
		
		$fileList = new HtaccessList();
		$fileList->readObjects();
		$this->availableFiles = $fileList->getObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'availableFiles' => $this->availableFiles,
			'objectTree' => $this->objectTree,
			'fileID' => $this->fileID,
			'file' => $this->file
		]);
	}
}
