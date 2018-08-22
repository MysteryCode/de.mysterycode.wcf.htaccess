<?php

namespace wcf\data\htaccess\content;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\data\package\PackageCache;
use wcf\system\application\ApplicationHandler;
use wcf\system\bbcode\highlighter\PlainHighlighter;
use wcf\system\exception\UserInputException;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * @method   HtaccessContentEditor       getSingleObject()
 * @method   HtaccessContentEditor[]     getObjects()
 * @property HtaccessContentEditor[]     $objects
 */
class HtaccessContentAction extends AbstractDatabaseObjectAction implements ISortableAction, IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.configuration.htaccess.canAddContent'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.configuration.htaccess.canAddContent'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.configuration.htaccess.canDeleteContent'];
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		WCF::getSession()->checkPermissions(['admin.configuration.htaccess.canEnableContent']);
		
		parent::validateUpdate();
	}
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->getObjects() as $object) {
			$object->update(['isDisabled' => $object->isDisabled ? 0 : 1]);
		}
	}
	
	/**
	 * @inheritDoc
	 * @throws \wcf\system\exception\UserInputException
	 */
	public function validateUpdatePosition() {
		WCF::getSession()->checkPermissions(['admin.configuration.htaccess.canSortContent']);
		
		// validate structure
		if (!isset($this->parameters['data']) || !isset($this->parameters['data']['structure']) || !is_array($this->parameters['data']['structure'])) {
			throw new UserInputException('structure');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function updatePosition() {
		$sql = "UPDATE	wcf".WCF_N."_htaccess_content
			SET	parentID = ?,
				showOrder = ?
			WHERE	contentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'] as $parentItemID => $children) {
			foreach ($children as $showOrder => $contentID) {
				$statement->execute([
					$parentItemID ?: null,
					$showOrder + 1,
					$contentID
				]);
			}
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Validates the getDetailDialog action
	 *
	 * @throws \wcf\system\exception\UserInputException
	 */
	public function validateGetDetailDialog() {
		$this->readObjects();
		
		if (empty($this->objects)) {
			throw new UserInputException('objectIDs');
		}
	}
	
	/**
	 * Returns the compiled template for the detail dialog
	 *
	 * @return string
	 */
	public function getDetailDialog() {
		$content = $this->getSingleObject();
		$tpl = TemplateEngine::getInstance();
		$lines = explode("\n", htmlentities($content->getOutput()));
		
		return WCF::getTPL()->fetch('htaccessContentDetailDialog', 'wcf', [
			'content' => $content,
			'package' => PackageCache::getInstance()->getPackageByIdentifier($content->getFile()->package),
			'application' => ApplicationHandler::getInstance()->getApplication($content->getFile()->application),
			'code' => $tpl->fetch('codeMetaCode', 'wcf', [
				'highlighter' => PlainHighlighter::getInstance(),
				'filename' => $content->getFile()->getShortPath(),
				'startLineNumber' => 1,
				'content' => $lines,
				'lines' => count($lines)
			])
		]);
	}
}
