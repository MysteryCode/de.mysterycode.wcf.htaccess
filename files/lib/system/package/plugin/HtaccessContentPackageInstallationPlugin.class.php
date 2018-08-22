<?php

namespace wcf\system\package\plugin;

use wcf\data\htaccess\content\HtaccessContent;
use wcf\data\htaccess\content\HtaccessContentEditor;
use wcf\data\htaccess\content\HtaccessContentList;
use wcf\system\application\ApplicationHandler;
use wcf\system\devtools\pip\IIdempotentPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

class HtaccessContentPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IIdempotentPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = HtaccessContentEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'content';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_htaccess_content
			WHERE		contentIdentifier = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['identifier'],
				$this->installation->getPackageID()
			]);
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * @inheritDoc
	 * @throws	SystemException
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$nodeValue = $element->nodeValue;
		
		$elements[$element->tagName] = $nodeValue;
	}
	
	protected function getFile(array $data) {
		if (empty($data['application'])) return null;
		
		$sql = "SELECT	fileID
				FROM	wcf".WCF_N."_htaccess
				WHERE	application = ?
					AND path = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute([$data['application'], $data['path']]);
		$row = $statement->fetchSingleRow();
		if ($row !== false) {
			return $row['fileID'];
		} else {
			if (empty($data['package']) === null) {
				throw new SystemException("Parameter 'package' is missing or incorrect.");
			}
			
			$statement = WCF::getDB()->prepareStatement("INSERT INTO wcf" . WCF_N . "_htaccess (package, path, application) VALUES (?,?,?)");
			$statement->execute([$data['package'], $data['path'], $data['application']]);
			return WCF::getDB()->getInsertID('wcf' . WCF_N . '_htaccess', 'fileID');
			//TODO check
		}
	}
	
	/**
	 * @inheritDoc
	 * @throws	SystemException
	 */
	protected function prepareImport(array $data) {
		if (!isset($data['elements']['path'])) $data['elements']['path'] = '';
		
		$fileID = null;
		if (!empty($data['elements']['application']) && isset($data['elements']['path']) && (!isset($data['elements']['global']) || !$data['elements']['global'])) {
			$fileID = $this->getFile($data['elements']);
			
			if ($fileID === null) throw new SystemException("Parameters 'application', 'path' or 'package' are missing or incorrect.");
		}
		
		$parentItemID = null;
		if (!empty($data['elements']['parent'])) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_htaccess_content
				WHERE	contentIdentifier = ?";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute([$data['elements']['parent']]);
			
			/** @var HtaccessContent|null $parent */
			$parent = $statement->fetchObject(HtaccessContent::class);
			if ($parent === null) {
				throw new SystemException("Unable to find parent item '" . $data['elements']['parent'] . "' for item '" . $data['attributes']['identifier'] . "'");
			}
			
			$parentItemID = $parent->contentID;
		}
		
		return [
			'contentIdentifier' => $data['attributes']['identifier'],
			'fileID' => $fileID,
			'application' => isset($data['elements']['application']) ? $data['elements']['application'] : null,
			'path' => isset($data['elements']['path']) ? $data['elements']['path'] : null,
			'package' => isset($data['elements']['package']) ? $data['elements']['package'] : null,
			'content' => isset($data['elements']['content']) ? $data['elements']['content'] : null,
			'options' => isset($data['elements']['options']) ? $data['elements']['options'] : null,
			'isGlobal' => isset($data['elements']['global']) ? $data['elements']['global'] : 0,
			'isUnique' => isset($data['elements']['unique']) ? $data['elements']['unique'] : 0,
			'isDynamic' => isset($data['elements']['isDynamic']) ? $data['elements']['isDynamic'] : 0,
			'forceSingleFile' => isset($data['elements']['force-file']) ? $data['elements']['force-file'] : 0,
			'controller' => isset($data['elements']['controller']) ? $data['elements']['controller'] : null,
			'module' => isset($data['elements']['module']) ? $data['elements']['module'] : null,
			'isSystem' => 1,
			'isDisabled' => 1,
			'parentID' => $parentItemID,
			'showOrder' => $this->getItemOrder($parentItemID)
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_htaccess_content
			WHERE	contentIdentifier = ?
				AND packageID = ?";
		$parameters = [
			$data['contentIdentifier'],
			$this->installation->getPackageID()
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function import(array $row, array $data) {
		if (isset($row['isGlobal']) && $row['isGlobal']) {
			unset($data['package']);
			unset($data['application']);
			unset($data['path']);
			unset($data['fileID']);
			unset($data['isDisabled']);
			unset($data['showOrder']);
			
			// update existing item
			$contentList = new HtaccessContentList();
			$contentList->getConditionBuilder()->add('htaccess_content.contentIdentifier = ?', [$row['contentIdentifier']]);
			$contentList->readObjects();
			
			$return = null;
			foreach ($contentList->getObjects() as $content) {
				/** @var HtaccessContentEditor $contentEditor */
				$contentEditor = new $this->className($content);
				$contentEditor->update($data);
				if ($content->fileID == null) $return = $contentEditor;
			}
			
			return $return;
		}
		else if (empty($row) && (isset($data['isGlobal']) && $data['isGlobal'])) {
			// create new item
			$this->prepareCreate($data);
			
			$ndata = $data;
			unset($data['package']);
			unset($data['application']);
			unset($data['path']);
			$return = call_user_func([$this->className, 'create'], $data);
			
			$t = empty($data['package']);
			foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
				if (!empty($data['isGlobal']) && $t) $ndata['package'] = $application->getPackage()->package;
				$ndata['application'] = $application->getAbbreviation();
				$data['fileID'] = $this->getFile($ndata);
				
				// create new item
				call_user_func([$this->className, 'create'], $data);
			}
			
			return $return;
		}
		else {
			unset($data['package']);
			unset($data['application']);
			unset($data['path']);
			
			return parent::import($row, $data);
		}
	}
	
	/**
	 * Returns the show order for a new item that will append it to the current
	 * menu or parent item.
	 *
	 * @param	integer		$parentItemID
	 * @return	integer
	 */
	protected function getItemOrder($parentItemID = null) {
		$sql = "SELECT	MAX(showOrder) AS showOrder
			FROM	wcf".WCF_N."_htaccess_content
			WHERE	parentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute([$parentItemID]);
		
		$row = $statement->fetchSingleRow();
		
		return (!$row['showOrder']) ? 1 : $row['showOrder'] + 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getSyncDependencies() {
		return ['language'];
	}
}
