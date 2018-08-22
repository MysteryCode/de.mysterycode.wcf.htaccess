{include file='header' pageTitle='wcf.acp.htaccess.content.'|concat:$action}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.htaccess.content.{$action}{/lang}</h1>
</header>

{include file='formError'}

<form id="htaccessContentAddForm" method="post" action="{if $action == 'add'}{link controller='HtaccessContentAdd'}{/link}{else}{link controller='HtaccessContentEdit' id=$contentID}{/link}{/if}">
	{if $action == 'add' || $contentObject->isEditable() == 1}
		<section class="section">
			<header class="sectionHeader">
				<h2 class="sectionTitle">{lang}wcf.acp.htaccess.content.file{/lang}</h2>
			</header>

			<dl{if $errorField == 'contentIdentifier'} class="formError"{/if}>
				<dt><label for="contentIdentifier">{lang}wcf.acp.htaccess.content.contentIdentifier{/lang}</label></dt>
				<dd>
					<input id="contentIdentifier" name="contentIdentifier" class="long" value="{$i18nPlainValues[contentIdentifier]}" required />
					{if $errorField == 'contentIdentifier'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.htaccess.content.contentIdentifier.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			{include file='multipleLanguageInputJavascript' elementIdentifier='contentIdentifier' forceSelection=false}

			<dl{if $errorField == 'application'} class="formError"{/if}>
				<dt><label for="application">{lang}wcf.acp.htaccess.content.application{/lang}</label></dt>
				<dd>
					<select id="application" name="application" class="medium" required>
						{foreach from=$availableApplications item=app}
							<option value="{$app->getAbbreviation()}"{if $app->abbreviation == $application} selected{/if}>{$app->getPackage()->getName()}</option>
						{/foreach}
					</select>
					{if $errorField == 'application'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.htaccess.content.application.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>

			<dl{if $errorField == 'path'} class="formError"{/if}>
				<dt><label for="path">{lang}wcf.acp.htaccess.content.path{/lang}</label></dt>
				<dd>
					<input type="text" id="path" name="path" class="medium" required value="{$path}" />
					{if $errorField == 'path'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.htaccess.content.path.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>

			{*
			<dl{if $errorField == 'package'} class="formError"{/if}>
				<dt><label for="package">{lang}wcf.acp.htaccess.content.package{/lang}</label></dt>
				<dd>
					<input type="text" id="package" name="package" class="medium" required value="{$package}" />
					{if $errorField == 'package'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.htaccess.content.package.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			*}

			<dl{if $errorField == 'package'} class="formError"{/if}>
				<dt><label for="package">{lang}wcf.acp.htaccess.content.package{/lang}</label></dt>
				<dd>
					<select id="package" name="package" class="medium" required>
						{foreach from=$availablePackages item=package}
							<option value="{$package->package}"{if $package->package == $package} selected{/if}>{$package->getName()}</option>
						{/foreach}
					</select>
					{if $errorField == 'package'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.htaccess.content.package.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		</section>
	{/if}

	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.htaccess.content.configuration{/lang}</h2>
		</header>

		{if $action == 'add' || $contentObject->isEditable() == 1}
			<dl{if $errorField == 'options'} class="formError"{/if}>
				<dt><label for="options">{lang}wcf.acp.htaccess.content.options{/lang}</label></dt>
				<dd>
					<textarea id="options" name="options" class="wide" rows="3">{','|str_replace:"\n":$options}</textarea>
					{if $errorField == 'options'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.htaccess.content.options.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>

			<dl{if $errorField == 'module'} class="formError"{/if}>
				<dt><label for="module">{lang}wcf.acp.htaccess.content.module{/lang}</label></dt>
				<dd>
					<input type="text" id="module" name="module" value="{$module}" class="medium" />
					{if $errorField == 'module'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.htaccess.content.module.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>

			<dl{if $errorField == 'content'} class="formError"{/if}>
				<dt><label for="content">{lang}wcf.acp.htaccess.content.content{/lang}</label></dt>
				<dd>
					<textarea id="content" name="content" class="wide" rows="10" required>{$content}</textarea>
					{if $errorField == 'content'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.htaccess.content.content.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		{/if}

		{if $action == 'edit' && $contentObject->controller}
			{hascontent}
				{content}{@$processor->getForm()}{/content}
			{hascontentelse}
				<div class="info">{lang}wcf.htaccess.content.edit.noConfigurationPossible{/lang}</div>
			{/hascontent}
		{/if}
	</section>

	{event name='sections'}

	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
