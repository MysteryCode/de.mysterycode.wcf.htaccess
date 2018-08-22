<?php

namespace wcf\acp\page;

use wcf\data\htaccess\content\HtaccessContentList;
use wcf\data\htaccess\content\RecursiveHtaccessContentList;
use wcf\data\option\OptionList;
use wcf\page\AbstractPage;
use wcf\system\application\ApplicationHandler;
use wcf\system\bbcode\highlighter\PlainHighlighter;
use wcf\system\Regex;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;

class HtaccessListPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.htaccess';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.htaccess.canView'];
	
	/**
	 * @var mixed[]
	 */
	protected $fileList = [];
	
	/**
	 * @var string[]
	 */
	protected $missingFiles = [];
	
	/**
	 * @var integer[]
	 */
	protected $missingFileIDs = [];
	
	/**
	 * @var \wcf\data\option\Option[]
	 */
	protected $optionList = [];
	
	/**
	 * @var \wcf\data\htaccess\content\HtaccessContent[]
	 */
	protected $contentList = [];
	
	/**
	 * @var string[]
	 */
	protected $generatedContentTree = [];
	
	/**
	 * @var string[]
	 */
	public $optionNames = [
		'url_omit_index_php',
		'url_title_component_replacement',
		'page_logo_link_to_app_default',
		'http_send_x_frame_options',
		'http_enable_gzip',
		
		'mcps_rewrite_enable_no',
		'mcps_htaccess_additional'
	];
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		parent::checkPermissions();
		
		WCFACP::checkMasterPassword();
	}
	
	/**
	 * @param $apps
	 * @param $path
	 * @return string
	 */
	protected function getCurApp($apps, $path) {
		$p = explode('/', $path);
		
		while (array_pop($p)) {
			$t = implode('/', $p) . '/';
			if (in_array($t, $apps)) {
				return array_search($t, $apps);
			}
		}
		
		return 'undefined';
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// get htaccess files and it's current contents
		$apps = ApplicationHandler::getInstance()->getAbbreviations();
		$directories = $paths = [];
		foreach ($apps as $abbreviation) {
			$directories[$abbreviation] = FileUtil::getRealPath(constant(strtoupper($abbreviation) . '_DIR'));
		}
		foreach ($directories as $app => $dir) {
			$du = new DirectoryUtil($dir);
			$files = $du->getFiles(SORT_ASC, new Regex('\.htaccess$'));
			foreach ($files as $path => $file) {
				if (in_array($file, $paths)) continue;
				$paths[] = $file;
				
				$content = htmlentities(file_get_contents($file));
				$content = explode("\n", $content);
				
				$curapp = $this->getCurApp($directories, $path);
				
				$tpl = TemplateEngine::getInstance();
				$this->fileList[$curapp][] = [
					'path' => $file,
					'content' => $tpl->fetch('codeMetaCode', 'wcf', [
						'highlighter' => PlainHighlighter::getInstance(),
						'filename' => $path,
						'startLineNumber' => 1,
						'content' => $content,
						'lines' => count($content)
					])
				];
			}
		}
		
		// get relevant options
		$optionList = new OptionList();
		$optionList->getConditionBuilder()->add('option_table.optionName IN (?)', [$this->optionNames]);
		$optionList->readObjects();
		$this->optionList = $optionList->getObjects();
		
		// get currently enabled and useable snippets
		$contentList = new HtaccessContentList();
		$contentList->getConditionBuilder()->add('htaccess_content.isDisabled <> 1');
		$contentList->readObjects();
		foreach ($contentList->getObjects() as $content) {
			//if ($content->isUseable() && !$content->isDisabled) $this->contentList[] = $content;
			if (!$content->isDisabled) $this->contentList[] = $content;
		}
		$applications = ApplicationHandler::getInstance()->getApplications();
		$m = [];
		foreach ($applications as $app) {
			$m[$app->getAbbreviation()] = FileUtil::getRealPath(constant(strtoupper($app->getAbbreviation()) . '_DIR'));
		}
		
		// get generated contents for files without updating the files
		$this->generatedContentTree = $this->getGenerationTree();
		
		// get a list of potential missing files
		$statement = WCF::getDB()->prepareStatement("SELECT h.* FROM wcf" . WCF_N . "_htaccess h WHERE h.isSecuring = 1 AND h.package IN (SELECT p.package FROM wcf" . WCF_N . "_package p)");
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$path = $m[$row['application']] . $row['path'] . '/.htaccess';
			if (!in_array($path, $paths)) {
				$this->missingFiles[] = str_replace('//', '/',$row['application'] . '/' . $row['path'] . '/.htaccess');
				$this->missingFileIDs[] = intval($row['fileID']);
			}
		}
	}
	
	/**
	 * @return string[]
	 */
	protected function getGenerationTree() {
		$contentList = new RecursiveHtaccessContentList();
		$contentList->readObjects();
		$objectTree = $contentList->getOutputTree();
		
		foreach ($objectTree as $path => $snippetList) {
			$tpl = TemplateEngine::getInstance();
			$content = explode("\n", htmlentities(implode(" \n \n",  $snippetList)));
			
			$objectTree[$path] = [
				'path' => $path,
				'content' => $tpl->fetch('codeMetaCode', 'wcf', [
					'highlighter' => PlainHighlighter::getInstance(),
					'filename' => $path,
					'startLineNumber' => 1,
					'content' => $content,
					'lines' => count($content)
				])
			];
		}
		
		return $objectTree;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'fileList' => $this->fileList,
			'optionList' => $this->optionList,
			'generatedContentList' => $this->generatedContentTree,
			'contentList' => $this->contentList,
			'missingFiles' => $this->missingFiles,
			'missingFileIDs' => $this->missingFileIDs,
			'applicationHandler' => ApplicationHandler::getInstance()
		]);
	}
}
