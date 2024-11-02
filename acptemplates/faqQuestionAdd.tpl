{include file='header' pageTitle='wcf.acp.menu.link.faq.questions.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.faq.questions.{$action}{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='FaqQuestionList'}{/link}" class="button">{icon name='list' size=16} <span>{lang}wcf.acp.menu.link.faq.questions.list{/lang}</span></a></li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $categories|count}
	{unsafe:$form->getHtml()}
{else}
	<woltlab-core-notice type="error">{lang}wcf.acp.faq.question.error.noCategory{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}