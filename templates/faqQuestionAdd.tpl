{capture assign='pageTitle'}{lang}wcf.acp.menu.link.faq.questions.{$action}{/lang}{/capture}

{capture assign='__contentHeader'}
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
{/capture}

{include file='header' contentHeader=$__contentHeader}

{unsafe:$form->getHtml()}

{include file='footer'}
