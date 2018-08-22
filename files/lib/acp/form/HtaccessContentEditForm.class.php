<?php

namespace wcf\acp\form;

use wcf\data\htaccess\content\HtaccessContent;
use wcf\data\htaccess\content\HtaccessContentAction;
use wcf\data\htaccess\content\HtaccessContentEditor;
use wcf\data\package\PackageCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

class HtaccessContentEditForm extends HtaccessContentAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.htaccess.content';
	/**
	 * @inheritDoc
	 */
	public $action = 'edit';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.htaccess.canView', 'admin.configuration.htaccess.canAddContent'];
	
	/**
	 * @var integer
	 */
	public $contentID;
	
	/**
	 * @var \wcf\data\htaccess\content\HtaccessContent
	 */
	public $contentObject;
	
	/**
	 * @var \wcf\system\htaccess\snippet\ISnippetProvider
	 */
	public $processor;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		if (!empty($_REQUEST['id'])) $this->contentID = intval($_REQUEST['id']);
		$this->contentObject = new HtaccessContent($this->contentID);
		if ($this->contentObject === null || !$this->contentObject->contentID) {
			throw new IllegalLinkException();
		}
		
		if (!$this->contentObject->isEditable()) {
			throw new PermissionDeniedException();
		}
		
		parent::readParameters();
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractAcpForm::save();
		
		$statement = WCF::getDB()->prepareStatement("SELECT * FROM wcf" . WCF_N . "_htaccess WHERE application = ? AND path = ?");
		$statement->execute([$this->application, $this->path]);
		$row = $statement->fetchArray();
		if ($row !== false) {
			$fileID = $row['fileID'];
		} else {
			$statement = WCF::getDB()->prepareStatement("INSERT INTO wcf" . WCF_N . "_htaccess (package, path, application) VALUES (?,?,?)");
			$statement->execute([$this->package, $this->path, $this->application]);
			$fileID = WCF::getDB()->getInsertID('wcf' . WCF_N . '_htaccess', 'fileID');
		}
		
		$data = [];
		
		if ($this->contentObject->isEditable() == 1) {
			$data = [
				'content' => $this->content,
				'module' => $this->module,
				'fileID' => $fileID
			];
		}
		$ado = $this->contentObject->additionalData ? @unserialize($this->contentObject->additionalData) : [];
		if (!is_array($ado)) $ado = [];
		$data['additionalData'] = serialize(array_merge($ado, $this->additionalData));
		
		$this->objectAction = new HtaccessContentAction([$this->contentObject], 'update', [
			'data' => $data
		]);
		$this->objectAction->executeAction();
		if ($this->contentObject->isEditable() == 1) $this->beforeSaveI18n($this->contentObject);
		
		WCF::getTPL()->assign('success', true);
		
		$this->saved();
		
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if ($this->contentObject->controller) {
			$this->processor = new $this->contentObject->controller($this->contentObject);
		}
		
		if (empty($_POST)) {
			$this->package = $this->contentObject->getFile()->package;
			$this->application = $this->contentObject->getFile()->application;
			$this->path = $this->contentObject->getFile()->path;
			
			$this->module = $this->contentObject->module ?: '';
			$this->content = $this->contentObject->content ?: '';
			$this->options = $this->contentObject->options ? explode(',', $this->contentObject->options) : '';
			$this->additionalData = $this->contentObject->additionalData ? @unserialize($this->contentObject->additionalData) : [];
			
			I18nHandler::getInstance()->setOptions(
				'contentIdentifier',
				PackageCache::getInstance()->getPackageID('de.mysterycode.wcf.htaccess'),
				'wcf.acp.htaccess.content.item.wcf.custom' . $this->contentObject->contentID,
				"wcf.acp.htaccess.content.item.wcf.custom.\d+"
			);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'processor' => $this->processor,
			'contentObject' => $this->contentObject,
			'contentID' => $this->contentID
		]);
	}
}
