DROP TABLE IF EXISTS wcf1_htaccess;
CREATE TABLE wcf1_htaccess (
	fileID         INT(10)    NOT NULL AUTO_INCREMENT PRIMARY KEY,
	package        VARCHAR(191),
	isSecuring     TINYINT(1) NOT NULL DEFAULT 0,
	defaultContent TEXT,
	path           VARCHAR(255),
	application    VARCHAR(191),

	KEY package (package)
);

DROP TABLE IF EXISTS wcf1_htaccess_content;
CREATE TABLE wcf1_htaccess_content (
	contentID         INT(10)      NOT NULL AUTO_INCREMENT PRIMARY KEY,
	contentIdentifier VARCHAR(191) NOT NULL DEFAULT '',
	isSystem          TINYINT(1)   NOT NULL DEFAULT 0,
	content           TEXT,
	module            TEXT,
	packageID         INT(10),
	showOrder         INT(10)      NOT NULL DEFAULT 0,
	options           TEXT,
	additionalData    TEXT,
	isGlobal          TINYINT(1)   NOT NULL DEFAULT 0,
	isUnique          TINYINT(1)   NOT NULL DEFAULT 0,
	isDynamic         TINYINT(1)   NOT NULL DEFAULT 0,
	isDisabled        TINYINT(1)   NOT NULL DEFAULT 0,
	forceSingleFile   TINYINT(1)   NOT NULL DEFAULT 0,
	controller        VARCHAR(255),
	parentID          INT(10),
	fileID            INT(10),

	KEY contentIdentifier (contentIdentifier)
);

ALTER TABLE wcf1_htaccess_content ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_htaccess_content ADD FOREIGN KEY (parentID) REFERENCES wcf1_htaccess_content (contentID) ON DELETE SET NULL;
ALTER TABLE wcf1_htaccess_content ADD FOREIGN KEY (fileID) REFERENCES wcf1_htaccess (fileID) ON DELETE CASCADE;

-- known htaccess locations
INSERT INTO wcf1_htaccess (package, isSecuring, defaultContent, path, application) VALUES
	-- woltlab
	('com.woltlab.wcf', 1, 'deny from all', 'acp/templates', 'wcf'),
	('com.woltlab.wcf', 1, 'deny from all', 'acp/uninstall', 'wcf'),
	('com.woltlab.wcf', 1, 'deny from all', 'attachments', 'wcf'),
	('com.woltlab.wcf', 1, 'deny from all', 'cache', 'wcf'),
	('com.woltlab.wcf', 1, 'order allow,deny
<Files ~ "\.(png|jpg|gif)$">
	allow from all
</Files>', 'images/proxy', 'wcf'),
	('com.woltlab.wcf', 1, '# This file ensures that these folders are created during an update.', 'images/trophy', 'wcf'),
	('com.woltlab.wcf', 1, 'deny from all', 'language', 'wcf'),
	('com.woltlab.wcf', 1, 'deny from all', 'lib', 'wcf'),
	('com.woltlab.wcf', 1, 'Deny from all', 'lib/system/api/ezyang/htmlpurifier/maintenance', 'wcf'),
	('com.woltlab.wcf', 1, 'deny from all', 'log', 'wcf'),
	('com.woltlab.wcf', 1, 'deny from all', 'media_files', 'wcf'),
	('com.woltlab.wcf', 1, 'deny from all', 'templates', 'wcf'),
	('com.woltlab.wcf', 1, 'deny from all', 'tmp', 'wcf'),

	('com.woltlab.blog', 1, 'deny from all', 'acp/templates', 'blog'),
	('com.woltlab.blog', 1, 'deny from all', 'lib', 'blog'),
	('com.woltlab.blog', 1, 'deny from all', 'templates', 'blog'),

	('com.woltlab.calendar', 1, 'deny from all', 'acp/templates', 'calendar'),
	('com.woltlab.calendar', 1, 'deny from all', 'lib', 'calendar'),
	('com.woltlab.calendar', 1, 'deny from all', 'templates', 'calendar'),

	('com.woltlab.filebase', 1, 'deny from all', 'acp/templates', 'filebase'),
	('com.woltlab.filebase', 1, 'deny from all', 'lib', 'filebase'),
	('com.woltlab.filebase', 1, 'deny from all', 'storage', 'filebase'),
	('com.woltlab.filebase', 1, 'deny from all', 'templates', 'filebase'),

	('com.woltlab.gallery', 1, 'deny from all', 'acp/templates', 'gallery'),
	('com.woltlab.gallery', 1, 'deny from all', 'lib', 'gallery'),
	('com.woltlab.gallery', 1, 'deny from all', 'storage', 'gallery'),
	('com.woltlab.gallery', 1, 'deny from all', 'templates', 'gallery'),

	('com.woltlab.wbb', 1, 'deny from all', 'acp/templates', 'wbb'),
	('com.woltlab.wbb', 1, 'deny from all', 'lib', 'wbb'),
	('com.woltlab.wbb', 1, 'deny from all', 'templates', 'wbb'),

	-- fabihome
	('de.fabihome.gamingsuite', 1, 'deny from all', 'acp/templates', 'gamingsuite'),
	('de.fabihome.gamingsuite', 1, 'deny from all', 'lib', 'gamingsuite'),
	('de.fabihome.gamingsuite', 1, 'deny from all', 'templates', 'gamingsuite'),

	('de.fabihome.clansuite', 1, 'deny from all', 'acp/templates', 'clansuite'),
	('de.fabihome.clansuite', 1, 'deny from all', 'lib', 'clansuite'),
	('de.fabihome.clansuite', 1, 'deny from all', 'templates', 'clansuite'),

	('de.fabihome.minecraft', 1, 'deny from all', 'acp/templates', 'minecraft'),
	('de.fabihome.minecraft', 1, 'deny from all', 'lib', 'minecraft'),
	('de.fabihome.minecraft', 1, 'deny from all', 'templates', 'minecraft'),

	-- mysterycode
	('de.mysterycode.cemetery', 1, 'deny from all', 'acp/templates', 'cemetery'),
	('de.mysterycode.cemetery', 1, 'deny from all', 'lib', 'cemetery'),
	('de.mysterycode.cemetery', 1, 'deny from all', 'templates', 'cemetery'),

	('de.mysterycode.inventar', 1, 'deny from all', 'acp/templates', 'inventar'),
	('de.mysterycode.inventar', 1, 'deny from all', 'lib', 'inventar'),
	('de.mysterycode.inventar', 1, 'deny from all', 'storage', 'inventar'),
	('de.mysterycode.inventar', 1, 'deny from all', 'templates', 'inventar'),

	('de.mysterycode.mcgb', 1, 'deny from all', 'acp/templates', 'mcgb'),
	('de.mysterycode.mcgb', 1, 'deny from all', 'lib', 'mcgb'),
	('de.mysterycode.mcgb', 1, 'deny from all', 'templates', 'mcgb'),

	('de.mysterycode.mcps', 0, null, '', 'mcps'),
	('de.mysterycode.mcps', 1, 'deny from all', 'acp/templates', 'mcps'),
	('de.mysterycode.mcps', 1, 'deny from all', 'lib', 'mcps'),
	('de.mysterycode.mcps', 1, 'deny from all', 'storage', 'mcps'),
	('de.mysterycode.mcps', 1, 'deny from all', 'templates', 'mcps'),

	('de.mysterycode.mcts', 1, 'deny from all', 'acp/templates', 'mcts'),
	('de.mysterycode.mcts', 1, 'deny from all', 'lib', 'mcts'),
	('de.mysterycode.mcts', 1, 'deny from all', 'templates', 'mcts'),

	('de.mysterycode.wcfps', 1, 'deny from all', 'acp/templates', 'wcfps'),
	('de.mysterycode.wcfps', 1, 'deny from all', 'lib', 'wcfps'),
	('de.mysterycode.wcfps', 1, 'deny from all', 'templates', 'wcfps'),

	-- viecode
	('com.viecode.api.dompdf', 1, 'deny from all', 'lib/system/api/ezyang/htmlpurifier/maintenance', 'wcf'),

	('com.viecode.filebase', 1, 'deny from all', 'acp/templates', 'filebase'),
	('com.viecode.filebase', 1, 'deny from all', 'lib', 'filebase'),
	('com.viecode.filebase', 1, 'deny from all', 'storage', 'filebase'),
	('com.viecode.filebase', 1, 'deny from all', 'templates', 'filebase'),

	('com.viecode.lexicon', 1, 'deny from all', 'acp/templates', 'lexicon'),
	('com.viecode.lexicon', 1, 'deny from all', 'lib', 'lexicon'),
	('com.viecode.lexicon', 1, 'deny from all', 'templates', 'lexicon'),

	('com.viecode.shop', 1, 'deny from all', 'acp/templates', 'shop'),
	('com.viecode.shop', 1, 'deny from all', 'lib', 'shop'),
	('com.viecode.shop', 1, 'deny from all', 'storage/invoice', 'shop'),
	('com.viecode.shop', 1, 'deny from all', 'storage/mail', 'shop'),
	('com.viecode.shop', 1, 'deny from all', 'storage/order', 'shop'),
	('com.viecode.shop', 1, 'deny from all', 'storage/products', 'shop'),
	('com.viecode.shop', 1, 'deny from all', 'storage/products/temp', 'shop'),
	('com.viecode.shop', 1, 'deny from all', 'storage/templates', 'shop'),
	('com.viecode.shop', 1, 'deny from all', 'templates', 'shop'),

	('com.viecode.marketplace', 1, 'deny from all', 'acp/templates', 'marketplace'),
	('com.viecode.marketplace', 1, 'deny from all', 'lib', 'marketplace'),
	('com.viecode.marketplace', 1, 'deny from all', 'templates', 'marketplace'),

	-- web-produktion
	('com.web-produktion.wpbt', 1, 'deny from all', 'acp/templates', 'wpbt'),
	('com.web-produktion.wpbt', 1, 'deny from all', 'lib', 'wpbt'),
	('com.web-produktion.wpbt', 1, 'deny from all', 'templates', 'wpbt'),

	-- codequake / mysterycode
	('de.codequake.cms', 1, 'deny from all', 'acp/templates', 'cms'),
	('de.codequake.cms', 1, 'deny from all', 'files', 'cms'),
	('de.codequake.cms', 1, 'deny from all', 'lib', 'cms'),
	('de.codequake.cms', 1, 'deny from all', 'templates', 'cms');