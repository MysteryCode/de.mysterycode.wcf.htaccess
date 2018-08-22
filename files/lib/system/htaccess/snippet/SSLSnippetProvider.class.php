<?php

namespace wcf\system\htaccess\snippet;

use wcf\data\htaccess\content\HtaccessContent;
use wcf\system\application\ApplicationHandler;
use wcf\system\io\RemoteFile;
use wcf\system\request\LinkHandler;
use wcf\util\HTTPRequest;

class SSLSnippetProvider implements ISnippetProvider {
	/**
	 * @var HtaccessContent
	 */
	protected $content;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(HtaccessContent $content = null) {
		$this->content = $content;
	}
	
	/**
	 * @inheritDoc
	 */
	public function generateOutput() {
		return $this->content->content;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getForm() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public static function isUseable() {
		if (!RemoteFile::supportsSSL()) return 'PHP does not support SSL';
		
		$applications = ApplicationHandler::getInstance()->getApplications();
		
		foreach ($applications as $application) {
			$url = str_replace('http://', 'https://', LinkHandler::getInstance()->getLink('CoreRewriteTest', [
				'application' => $application->getAbbreviation(),
				'uuidHash' => hash('sha256', WCF_UUID),
				'forceFrontend' => true
			]));
			//$url = str_replace('http://', 'https://', $application->getPageURL());
			$request = new HTTPRequest($url);
			try {
				$request->execute();
			}
			catch (\Exception $e) {
				return $e->getMessage() . '(' . $url . ')';
			}
		}
		
		return true;
	}
}
