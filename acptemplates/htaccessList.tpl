{include file='header' pageTitle='wcf.acp.htaccess.list'}

<noscript>
	<style type="text/css">.jsApplicationHtaccessList { display: block; }</style>
</noscript>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/TabMenu'], function(UiTabMenu) {
		UiTabMenu.setup();

		$('.jsApplicationHtaccessListToggle').click(function (event) {
			let app = $(event.currentTarget).toggleClass('fa-chevron-down').toggleClass('fa-chevron-up').data('application');
			$('.jsApplicationHtaccessList[data-application="'+app+'"]').toggleClass('open');
		});
	});
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.htaccess.list{/lang}</h1>
</header>

{if !$missingFiles|empty}
	<div class="error">{lang}wcf.acp.htaccess.missingItems{/lang}</div>

	<script data-relocate="true">
		require(['MysteryCode/Code/Htaccess/QuickFixMissing'], function (QuickFix) {
			new QuickFix('.secureGenerationTrigger', [ {', '|implode:$missingFileIDs} ]);
		});
	</script>
{/if}

<div class="section tabMenuContainer" data-active="files" data-store="activeTabMenuItem">
	<nav class="tabMenu">
		<ul>
			<li><a href="{@$__wcf->getAnchor('files')}">{lang}wcf.acp.htaccess.files{/lang}</a></li>
			<li><a href="{@$__wcf->getAnchor('generatedFiles')}">{lang}wcf.acp.htaccess.content.generatedFiles{/lang}</a></li>
			<li><a href="{@$__wcf->getAnchor('contents')}">{lang}wcf.acp.htaccess.contents{/lang}</a></li>
			<li><a href="{@$__wcf->getAnchor('options')}">{lang}wcf.acp.htaccess.options{/lang}</a></li>

			{event name='tabMenuTabs'}
		</ul>
	</nav>

	<div id="files" class="section hidden tabMenuContent">
		<br>
		{foreach from=$fileList key=$application item=files}
			<section class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">
						<span class="jsOnly jsApplicationHtaccessListToggle icon icon16 fa-chevron-down pointer" data-application="{$application}"></span>
						{if $application != 'undefined'}{$applicationHandler->getApplication($application)->getPackage()->getName()}{else}{$application}{/if}
					</h2>
				</header>

				<div class="jsApplicationHtaccessList" data-application="{$application}">
					{foreach from=$files item=file}
						{@$file[content]}
					{/foreach}
				</div>
			</section>
		{foreachelse}
			<div class="info">{lang}wcf.global.noItems{/lang}</div>
		{/foreach}
	</div>

	<div id="generatedFiles" class="section hidden tabMenuContent">
		{foreach from=$generatedContentList key=$path item=file}
			{@$file[content]}
		{foreachelse}
			<div class="info">{lang}wcf.global.noItems{/lang}</div>
		{/foreach}
	</div>

	<div id="contents" class="section hidden tabMenuContent">
		{hascontent}
			<dl class="flexedHtaccessList">
				{content}
					{foreach from=$contentList item=content}
						<dt><span class="icon icon16 green fa-check"></span></dt>
						<dd>
							<span class="htaccessContentItemBody">
								<span class="htaccessContentItemTrigger">{lang}wcf.acp.htaccess.content.item.{$content->contentIdentifier}{/lang}</span>
								{if $content->fileID}<span class="badge label blue htaccessContentItemLabel">{$content->getApplicationName()}</span>{/if}
								<small>{lang __optional=true}wcf.acp.htaccess.content.item.{$content->contentIdentifier}.description{/lang}</small>
							</span>
						</dd>
					{/foreach}
				{/content}
			</dl>
		{hascontentelse}
			<div class="info">{lang}wcf.global.noItems{/lang}</div>
		{/hascontent}
	</div>

	<div id="options" class="section hidden tabMenuContent">
		{hascontent}
			<dl class="flexedHtaccessList">
				{content}
					{foreach from=$optionList item=option}
						<dt>{lang}wcf.acp.option.{$option->optionName}{/lang}</dt>
						<dd>
							{if $option->optionType == 'boolean'}
								<span class="icon icon16 {if $option->optionValue}green fa-check{else}red fa-times{/if}"></span>
							{elseif $option->optionType == 'textarea'}
								<pre class="framedMultilineValue">{$option->optionValue}</pre>
							{else}
								<span class="inlineCode">{$option->optionValue}</span>
							{/if}
							<small>{lang __optional=true}wcf.acp.option.{$option->optionName}.description{/lang}</small>
						</dd>
					{/foreach}
				{/content}
			</dl>
		{hascontentelse}
			<div class="info">{lang}wcf.global.noItems{/lang}</div>
		{/hascontent}
	</div>

	{event name='tabMenuContents'}
</div>

{include file='footer'}
