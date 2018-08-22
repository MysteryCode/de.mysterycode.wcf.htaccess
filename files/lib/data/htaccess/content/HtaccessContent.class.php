<?php

namespace wcf\data\htaccess\content;

use wcf\data\DatabaseObject;
use wcf\data\htaccess\Htaccess;
use wcf\data\package\PackageCache;
use wcf\data\TDatabaseObjectOptions;
use wcf\system\application\ApplicationHandler;

/**
 * @property-read integer $contentID
 * @property-read string  $contentIdentifier
 * @property-read integer $packageID
 * @property-read boolean $isSystem
 * @property-read boolean $isDynamic
 * @property-read boolean $isDisabled
 * @property-read boolean $forceSingleFile
 * @property-read boolean $isUnique
 * @property-read boolean $isGlobal
 * @property-read string  $options
 * @property-read string  $controller
 * @property-read integer $parentID
 * @property-read integer $fileID
 * @property-read string  $content
 * @property-read string  $module
 * @property-read string  $additionalData
 */
class HtaccessContent extends DatabaseObject {
	use TDatabaseObjectOptions;
	
	/**
	 * @var boolean
	 */
	protected $isUseable;
	
	/**
	 * @var string[]
	 */
	protected $failReasons = [];
	
	/**
	 * @var Htaccess
	 */
	protected $file;
	
	/**
	 * Returns 1 if the user can modify the content's code
	 * Returns 2 if the user can configure some settings of the dynamic snippet
	 * Returns false if the snippet is not dynamic and created by system
	 *
	 * @return boolean|1|2
	 */
	public function isEditable() {
		if ($this->isSystem && $this->isDynamic) return 2;
		if (!$this->isSystem) return 1;
		
		return false;
	}
	
	/**
	 * Returns true if the user can delete the content's code
	 *
	 * @return boolean
	 */
	public function isDeleteable() {
		return !$this->isSystem;
	}
	
	/**
	 * Returns the output of the content object
	 *
	 * @param HtaccessContent $previous
	 * @param HtaccessContent $next
	 * @return string
	 */
	public function getOutput(HtaccessContent $previous = null, HtaccessContent $next = null) {
		$output = '';
		$t = $previous === null || $previous->isDynamic || $previous->module != $this->module;
		$n = $next === null || $next->isDynamic || $next->module != $this->module;
		
		$wsc = ApplicationHandler::getInstance()->getApplication('wcf');
		$application = ApplicationHandler::getInstance()->getApplication($this->getFile()->application);
		
		if (!$this->isDynamic) {
			if ($this->module && $t) $output .= "<IfModule " . $this->module . ">\n";
			//todo
			$output .= ($this->module ? "\t" : "") . "### Snippet ID: " . $this->contentID . " // " . $this->contentIdentifier . "\n";
			$output .= str_replace(['::OLD_APP_DIR', '::WSC_DIR::', '::APP_DIR::', '::APP_NAME::'], ['', $wsc->domainPath, substr($application->domainPath, 1), $application->getPackage()->getName()], $this->content);
			if ($this->module && $n) $output .= "\n</IfModule>";
		} else {
			/** @var \wcf\system\htaccess\snippet\ISnippetProvider $provider */
			$provider = new $this->controller($this);
			$output .= "### Snippet ID: " . $this->contentID . " // " . $this->contentIdentifier . "\n";
			$output .= $provider->generateOutput();
		}
		
		return $output;
	}
	
	/**
	 * @return Htaccess
	 */
	public function getFile() {
		if ($this->file === null) {
			$this->file = new Htaccess($this->fileID);
		}
		
		return $this->file;
	}
	
	/**
	 * Returns non-disabled contents whichs requirements are fullfilled
	 *
	 * @return boolean
	 */
	public function isUseable() {
		if ($this->isUseable === null) {
			$result = true;
			
			if (!$this->validateOptions()) {
				$this->failReasons[] = 'Not all options are enabled.';
				$result = false;
			}
			
			if ($this->fileID && PackageCache::getInstance()->getPackageByIdentifier($this->getFile()->package) === null) {
				$this->failReasons[] = 'Package ' . $this->getFile()->package . ' is currently not installed.';
				$result = false;
			}
			
			if ($this->isDynamic && !class_exists($this->controller)) {
				$this->failReasons[] = 'Controller ' . $this->controller . ' can not be found.';
				$result = false;
			}
			
			if ($this->isDynamic) {
				/** @noinspection PhpUndefinedMethodInspection */
				$test = $this->controller::isUseable();
				
				if ($test !== true) {
					$this->failReasons[] = 'Individual test: ' . $test;
					$result = false;
				}
			}
			
			$this->isUseable = $result;
		}
		
		return $this->isUseable;
	}
	
	/**
	 * Returns a string of reasons why the provider can't be used
	 *
	 * @return string[]
	 */
	public function getFailReasons() {
		$this->isUseable();
		
		return $this->failReasons;
	}
	
	/**
	 * @return string
	 */
	public function getApplicationName() {
		$application = ApplicationHandler::getInstance()->getApplication($this->getFile()->application);
		return $application->getPackage()->getName();
	}
}
