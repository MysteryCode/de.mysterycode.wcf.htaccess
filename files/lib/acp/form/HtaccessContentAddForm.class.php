<?php

namespace wcf\acp\form;

use wcf\data\htaccess\content\HtaccessContentAction;
use wcf\data\htaccess\content\HtaccessContentEditor;
use wcf\data\htaccess\HtaccessList;
use wcf\data\package\PackageCache;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nValue;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;

class HtaccessContentAddForm extends AbstractAcpForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.htaccess.content.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.htaccess.canView', 'admin.configuration.htaccess.canAddContent'];
	
	/**
	 * abbreviation of the application
	 *
	 * @var string
	 */
	public $application = '';
	
	/**
	 * identifier of the needed package that provides the .htaccess-file
	 *
	 * @var string
	 */
	public $package = '';
	
	/**
	 * relative path from application's root to .htaccess-file without filename
	 *
	 * @var string
	 */
	public $path = '';
	
	/**
	 * needed options
	 *
	 * @var string|string[]
	 */
	public $options = [];
	
	/**
	 * needed module
	 *
	 * @var string
	 */
	public $module = '';
	
	/**
	 * rules to write into the htaccess
	 *
	 * @var string
	 */
	public $content = '';
	
	/**
	 * additional data
	 *
	 * @var mixed[]
	 */
	public $additionalData = [];
	
	/**
	 * @var \wcf\data\application\Application[]
	 */
	public $availableApplications;
	
	/**
	 * @var \wcf\data\package\Package
	 */
	public $availablePackages;
	
	/**
	 * @var \wcf\data\htaccess\Htaccess[]
	 */
	public $availableFiles;
	
	/**
	 * @var string
	 */
	public $identifier = '';
	
	/**
	 * @var string
	 */
	public $contentIdentifier = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$contentIdentifierI18n = new I18nValue('contentIdentifier');
		$contentIdentifierI18n->setLanguageItem('wcf.acp.htaccess.content.item.wcf.custom', 'wcf.acp.htaccess', 'de.mysterycode.wcf.htaccess');
		$this->registerI18nValue($contentIdentifierI18n);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['application'])) $this->application = StringUtil::trim(MessageUtil::stripCrap($_POST['application']));
		if (isset($_POST['package'])) $this->package = StringUtil::trim(MessageUtil::stripCrap($_POST['package']));
		if (isset($_POST['path'])) $this->path = StringUtil::trim(MessageUtil::stripCrap($_POST['path']));
		if ($this->path == '/') $this->path = '';
		if (isset($_POST['options'])) $this->options = explode("\n", StringUtil::unifyNewlines(StringUtil::trim(MessageUtil::stripCrap($_POST['options']))));
		if (isset($_POST['content'])) $this->content = StringUtil::trim(MessageUtil::stripCrap($_POST['content']));
		if (isset($_POST['module'])) $this->module = StringUtil::trim(MessageUtil::stripCrap($_POST['module']));
		if (isset($_POST['additionalData']) && is_array($_POST['additionalData'])) $this->additionalData = ArrayUtil::trim(MessageUtil::stripCrap($_POST['additionalData']));
	}
	
	/**
	 * @inheritDoc
	 * @throws \wcf\system\exception\UserInputException
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->content)) {
			throw new UserInputException('content');
		}
		
		if (empty($this->application)) {
			throw new UserInputException('application');
		}
		if (empty($this->package)) {
			throw new UserInputException('package');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
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
		
		$this->objectAction = new HtaccessContentAction([], 'create', [
			'data' => [
				'contentIdentifier' => StringUtil::getRandomID(),
				'content' => $this->content,
				'module' => $this->module,
				'options' => implode(',', $this->options),
				'fileID' => $fileID,
				'additionalData' => serialize($this->additionalData)
			]
		]);
		$content = $this->objectAction->executeAction()['returnValues'];
		(new HtaccessContentEditor($content))->update(['contentIdentifier' => 'wcf.custom' . $content->contentID]);
		
		$this->beforeSaveI18n($content);
		
		WCF::getTPL()->assign('success', true);
		
		$this->saved();
		
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		$this->availableApplications = ApplicationHandler::getInstance()->getApplications();
		$this->availablePackages = PackageCache::getInstance()->getPackages();
		
		$fileList = new HtaccessList();
		$fileList->readObjects();
		$this->availableFiles = $fileList->getObjects();
		
		parent::readData();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'availableFiles' => $this->availableFiles,
			'availableApplications' => $this->availableApplications,
			'availablePackages' => $this->availablePackages,
			
			'additionalData' => $this->additionalData,
			'content' => $this->content,
			'module' => $this->module,
			'options' => $this->options ? implode(',', $this->options) : '',
			'application' => $this->application,
			'package' => $this->package,
			'path' => $this->path ?: '/',
		]);
	}
}
