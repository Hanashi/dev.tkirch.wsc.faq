{capture assign='__contentHeader'}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$__wcf->getActivePage()->getTitle()}</h1>
		</div>

		<nav class="contentHeaderNavigation">
			<ul>
				{if $__wcf->session->getPermission('admin.faq.canAddQuestion')}
					<li><a href="{link controller='FaqQuestionAdd'}{/link}" class="button">{icon name='plus' size=16} <span>{lang}wcf.acp.menu.link.faq.questions.add{/lang}</span></a></li>
				{/if}

				{event name='contentHeaderNavigation'}
			</ul>
		</nav>
	</header>
{/capture}

{include file='header' contentHeader=$__contentHeader}

{if $faqs|count}
	{if SIMPLE_FAQ_VIEW === 'gallery'}
		<div class="faqGallery">
			{foreach from=$faqs item=faq}
				{if ($faq['questions']|isset && $faq['questions']|count)}
					<button type="button" class="button galleryButton" data-id="{$faq['category']->categoryID}">
						{unsafe:$faq['category']->getIcon(64, true)}
						{$faq['category']->getTitle()}
					</button>
				{/if}
			{/foreach}
			<div class="gallerySpacer"></div>
			<div class="gallerySpacer"></div>
			<div class="gallerySpacer"></div>
			<div class="gallerySpacer"></div>
			<div class="gallerySpacer"></div>
			<div class="gallerySpacer"></div>
			<div class="gallerySpacer"></div>
			<div class="gallerySpacer"></div>
		</div>
		{foreach from=$faqs item=faq}
			{if ($faq['questions']|isset && $faq['questions']|count)}
				{assign var='attachmentList' value=$faq['attachments']}

				<div id="faqSection{$faq['category']->categoryID}" class="section faqGallerySection jsObjectActionContainer faq" data-object-action-class-name="wcf\data\faq\QuestionAction" style="display: none;">
					<h2>{unsafe:$faq['category']->getIcon(24)} {$faq['category']->getTitle()}</h2>

					{if $faq['questions']|isset}
						{foreach from=$faq['questions'] item=question}
							{include file='__faqQuestionListEntry'}
						{/foreach}
					{/if}
				</div>
			{/if}
		{/foreach}
	{else}
		<div class="section jsObjectActionContainer{if SIMPLE_FAQ_VIEW === 'cleave'} faqCleave{/if}" data-object-action-class-name="wcf\data\faq\QuestionAction">
			{foreach from=$faqs item=faq}
				{if ($faq['questions']|isset && $faq['questions']|count) || ($faq['sub']|isset && $faq['sub']|count)}
					{assign var='attachmentList' value=$faq['attachments']}

					<div class="section faq{if SIMPLE_FAQ_VIEW === 'cleave'} cleaveCategory{/if}">
						<header class="sectionHeader">
							<h2 class="sectionTitle">{unsafe:$faq['category']->getIcon(24)} {$faq['category']->getTitle()}</h2>
							<p class="sectionDescription">{unsafe:$faq['category']->getDescriptionHtml()}</p>
						</header>

						{if $faq['questions']|isset}
							{foreach from=$faq['questions'] item=question}
								{include file='__faqQuestionListEntry'}
							{/foreach}
						{/if}

						{if $faq['sub']|isset && $faq['sub']|count}
							{foreach from=$faq['sub'] item=sub}
								{if $sub['questions']|isset && $sub['questions']|count}
									{assign var='attachmentList' value=$sub['attachments']}

									<div class="sub">
										<header class="sectionHeader">
											<h2 class="sectionTitle">{unsafe:$sub['category']->getIcon(24)} {$sub['category']->getTitle()}</h2>
											<p class="sectionDescription">{unsafe:$sub['category']->getDescriptionHtml()}</p>
										</header>

										{foreach from=$sub['questions'] item=question}
											{include file='__faqQuestionListEntry'}
										{/foreach}
									</div>
								{/if}
							{/foreach}
						{/if}
					</div>
				{/if}
			{/foreach}
		</div>
	{/if}
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

<script data-relocate="true">
	require(["Hanashi/Faq/Question"], function (Question) {
		Question.init();
		{if SIMPLE_FAQ_VIEW === 'gallery'}
			Question.initGallery();
		{/if}
	});
</script>

{include file='faqQuestionAddDialog'}

{include file='footer'}