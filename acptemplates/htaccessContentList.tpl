{include file='header' pageTitle='wcf.acp.htaccess.content.list'}

<script data-relocate="true">
	require(['MysteryCode/Code/Htaccess/SnippetDetailDialog', 'Language'], function (SnippetDetailDialog, Language) {
		{if $__wcf->session->getPermission('admin.configuration.htaccess.canDeleteContent')}new WCF.Action.Delete('wcf\\data\\htaccess\\content\\HtaccessContentAction', '.jsContent');{/if}
		{if $__wcf->session->getPermission('admin.configuration.htaccess.canEnableContent')}new WCF.Action.Toggle('wcf\\data\\htaccess\\content\\HtaccessContentAction', '.jsContent');{/if}
		new SnippetDetailDialog('.jsInfoHtaccessContentTrigger');
		Language.addObject({ 'wcf.acp.htaccess.content.info' : '{lang}wcf.acp.htaccess.content.info{/lang}' });
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.htaccess.content.list{/lang}</h1>
	</div>

	{pages print=true assign=pagesLinks controller='HtaccessContentList' link="pageNo=%d"}

	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if !$availableFiles|empty}
						<li class="dropdown">
							<a class="button dropdownToggle"><span class="icon icon16 fa-sort"></span> <span>{lang}wcf.acp.htaccess.content.choose{/lang}</span></a>

							<div class="dropdownMenu">
								<ul class="scrollableDropdownMenu">
									{foreach from=$availableFiles item=availableFile}
										<li{if $availableFile->fileID == $fileID} class="active"{/if}><a href="{link controller='HtaccessContentList' id=$availableFile->fileID}{/link}">{$availableFile->getShortPath()}</a></li>
									{/foreach}
								</ul>
							</div>
						</li>
					{/if}

					{if $__wcf->session->getPermission('admin.configuration.htaccess.canAddContent')}<li><a href="{link controller='HtaccessContentAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.htaccess.content.add{/lang}</span></a></li>{/if}

					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{if $items}
	{foreach from=$objectTree key=path item=objectList}
		<div class="section sortableListContainer" id="contentList{$objectList[0]->fileID}" data-object-id="{$objectList[0]->fileID}">
			<header class="sectionHeader">
				<h2 class="sectionTitle">
					<a href="{link controller='HtaccessContentList' id=$objectList[0]->fileID}{/link}" class="icon icon24 fa-search" style="float: right;"></a>
					{$path}
				</h2>
			</header>

			<ol class="sortableList" data-object-id="0" start="{@($pageNo - 1) * $itemsPerPage + 1}">
				{foreach from=$objectList item=content}
					<li class="sortableNode sortableNoNesting jsContent" data-object-id="{@$content->contentID}">
						<span class="sortableNodeLabel">
							<span class="htaccessContentItemBody">
								<a class="htaccessContentItemTrigger">{lang}wcf.acp.htaccess.content.item.{$content->contentIdentifier}{/lang}</a>
								{if $content->fileID}<span class="badge label blue htaccessContentItemLabel">{$content->getApplicationName()}</span>{/if}
								<small>{lang __optional=true}wcf.acp.htaccess.content.item.{$content->contentIdentifier}.description{/lang}</small>
							</span>

							<span class="statusDisplay sortableButtonContainer">
								<span class="icon icon16 fa-arrows sortableNodeHandle"></span>

								{if $content->isDeleteable() && $__wcf->session->getPermission('admin.configuration.htaccess.canDeleteContent')}<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$content->contentID}" data-confirm-message-html="{lang __encode=true}wcf.acp.htaccess.content.delete.confirmMessage{/lang}"></span>{/if}
								{if $content->isEditable() !== false && $__wcf->session->getPermission('admin.configuration.htaccess.canAddContent')}<a href="{link controller='HtaccessContentEdit' id=$content->contentID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>{/if}
								{if $content->isUseable()}
									{if $__wcf->session->getPermission('admin.configuration.htaccess.canEnableContent')}
										<span class="icon icon16 fa-{if !$content->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $content->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$content->contentID}"></span>
									{else}
										<span class="icon icon16 fa-{if !$content->isDisabled}check-{/if}square-o disabled"></span>
									{/if}
									<span class="icon icon16 fa-info-circle jsTooltip pointer jsInfoHtaccessContentTrigger" data-object-id="{$content->contentID}" title="{lang}wcf.acp.htaccess.content.info{/lang}"></span>
									<span class="icon icon16 fa-check-circle-o jsTooltip green pointer" title="{lang}wcf.acp.htaccess.content.useable{/lang}"></span>
								{else}
									<span class="icon icon16 fa-times-circle-o jsTooltip red pointer" title="{lang}wcf.acp.htaccess.content.notuseable{/lang}"></span>
								{/if}

								{event name='itemButtons'}
							</span>
						</span>
					</li>
				{/foreach}
			</ol>

			<div class="formSubmit">
				<button class="button" data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
			</div>
		</div>

		{if $__wcf->session->getPermission('admin.configuration.htaccess.canSortContent')}
			<script data-relocate="true">
				require(['WoltLabSuite/Core/Ui/Sortable/List'], function (UiSortableList) {
					new UiSortableList({
						containerId: 'contentList{$objectList[0]->fileID}',
						className: 'wcf\\data\\htaccess\\content\\HtaccessContentAction',
						offset: {@$startIndex}
					});
				});
			</script>
		{/if}
	{/foreach}

	<div class="contentNavigation">
		{@$pagesLinks}

		<nav>
			<ul>
				{if $__wcf->session->getPermission('admin.configuration.htaccess.canAddContent')}<li><a href="{link controller='HtaccessContentAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.htaccess.content.add{/lang}</span></a></li>{/if}
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<div class="info">{lang}wcf.global.noItems{/lang}</div>
{/if}

{include file='footer'}
