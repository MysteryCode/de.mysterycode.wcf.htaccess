<div class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.acp.htaccess.content.configuration{/lang}</h2>
	</header>

	<dl class="flexedHtaccessList">
		<dt>{lang}wcf.acp.htaccess.content.package{/lang}</dt>
		<dd>
			{if $content->getFile()->package}
				{if $package}
					{$package->getName()}<br>
				{/if}
				<span class="inlineCode">{$content->getFile()->package}</span>
			{else}
				{lang}wcf.acp.htaccess.content.global{/lang}
			{/if}
		</dd>

		<dt>{lang}wcf.acp.htaccess.content.application{/lang}</dt>
		<dd>
			{if $content->getFile()->package}
				{if $application}
					{$application->getPackage()->getName()} (<span class="inlineCode">{$content->getFile()->application}</span>)<br>
					<span class="inlineCode">{$application->domainName}{$application->domainPath}</span>
				{else}
					<span class="inlineCode">{$content->getFile()->application}</span>
				{/if}
			{else}
				{lang}wcf.acp.htaccess.content.global{/lang}
			{/if}
		</dd>

		<dt>{lang}wcf.acp.htaccess.content.path.fil{/lang}</dt>
		<dd><span class="inlineCode">{$content->getFile()->path}/.htaccess</span></dd>

		<dt>{lang}wcf.acp.htaccess.content.module{/lang}</dt>
		<dd>
			{if $content->module}<span class="inlineCode">{$content->module}</span>{else}<span class="icon icon16 fa-times"></span>{/if}
		</dd>

		<dt>{lang}wcf.acp.htaccess.content.options{/lang}</dt>
		<dd>
			{if $content->options}<span class="inlineCode">{$content->options}</span>{else}<span class="icon icon16 fa-times"></span>{/if}
		</dd>
	</dl>
</div>

<div class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.acp.htaccess.content.code{/lang}</h2>
	</header>

	{@$code}
</div>
