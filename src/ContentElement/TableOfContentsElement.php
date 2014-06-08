<?php

/*
 * This file is part of the "Table of contents element" package.
 *
 * (c) Tristan Lins <tristan.lins@bit3.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bit3\Contao\TableOfContentsElement\ContentElement;

use Bit3\Contao\XNavigation\Article\Condition\ArticleGroupsCondition;
use Bit3\Contao\XNavigation\Article\Condition\ArticleGuestsCondition;
use Bit3\Contao\XNavigation\Article\Condition\ArticleProtectedCondition;
use Bit3\Contao\XNavigation\Article\Condition\ArticlePublishedCondition;
use Bit3\Contao\XNavigation\Article\Provider\ArticleProvider;
use Bit3\Contao\XNavigation\Condition\MemberLoginCondition;
use Bit3\Contao\XNavigation\Content\Condition\ContentGroupsCondition;
use Bit3\Contao\XNavigation\Content\Condition\ContentGuestsCondition;
use Bit3\Contao\XNavigation\Content\Condition\ContentProtectedCondition;
use Bit3\Contao\XNavigation\Content\Condition\ContentPublishedCondition;
use Bit3\Contao\XNavigation\Content\Provider\ContentProvider;
use Bit3\Contao\XNavigation\XNavigationEvents;
use Bit3\FlexiTree\Condition\AndCondition;
use Bit3\FlexiTree\Condition\LevelCondition;
use Bit3\FlexiTree\Condition\OrCondition;
use Bit3\FlexiTree\Condition\TypeCondition;
use Bit3\FlexiTree\EventDrivenItemFactory;
use Bit3\FlexiTree\Item;
use Bit3\FlexiTree\ItemCollection;
use Symfony\Component\EventDispatcher\EventDispatcher;

class TableOfContentsElement extends \TwigContentElement
{

	/**
	 * Template
	 *
	 * @var string
	 */
	protected $strTemplate = 'ce_content_navigation';

	protected function compile()
	{
		// create local event dispatcher and provider
		$eventDispatcher = new EventDispatcher();
		$eventDispatcher->addSubscriber(new ArticleProvider());
		$eventDispatcher->addSubscriber(new ContentProvider());

		// create the event driven menu
		$eventDrivenItemFactory = new EventDrivenItemFactory(
			$eventDispatcher,
			XNavigationEvents::CREATE_ITEM,
			XNavigationEvents::COLLECT_ITEMS
		);

		$item  = new Item();
		$items = $item->getChildren();

		if ($this->toc_source == 'sections') {
			global $objPage;

			$sections = deserialize($this->toc_sections, true);
			$where    = array();
			$where[]  = '(' . implode(' OR ', array_fill(0, count($sections), 'inColumn=?')) . ')';
			$where[]  = 'pid=?';

			$args = array_merge($sections, array($objPage->id), $sections);

			$order = 'FIELD(tl_article.inColumn,' . implode(',', array_fill(0, count($sections), '?')) . ')';

			$articles = \ArticleModel::findBy(
				$where,
				$args,
				array('order' => $order)
			);

			if ($articles) {
				foreach ($articles as $article) {
					/** @var \ArticleModel $article */
					$item = $eventDrivenItemFactory->createItem('article', $article->id);

					if ($this->toc_include_articles) {
						$items->add($item);
					}
					else {
						$items->addAll($item->getChildren()->toArray());
					}
				}
			}
		}

		if ($this->toc_source == 'articles') {
			$articleIds = deserialize($this->toc_articles, true);

			foreach ($articleIds as $articleId) {
				$item = $eventDrivenItemFactory->createItem('article', $articleId);

				if ($this->toc_include_articles) {
					$items->add($item);
				}
				else {
					$items->addAll($item->getChildren()->toArray());
				}
			}
		}

		$this->Template->xnav_template = $this->xnavigation_template;
		$this->Template->items         = $items;
		$this->Template->item_condition = $this->createItemCondition();
		$this->Template->link_condition = $this->createLinkCondition();
	}

	public function createItemCondition()
	{
		$root = new AndCondition();

		{
			$or = new OrCondition();
			$root->addCondition($or);

			{
				$and = new AndCondition();
				$or->addCondition($and);

				$typeCondition = new TypeCondition('article');
				$and->addCondition($typeCondition);

				$publishedCondition = new ArticlePublishedCondition();
				$and->addCondition($publishedCondition);

				$loginStatusOr = new OrCondition();
				$and->addCondition($loginStatusOr);

				{
					$unprotectedAnd = new AndCondition();
					$loginStatusOr->addCondition($unprotectedAnd);

					{
						$protectedCondition = new ArticleProtectedCondition(false);
						$unprotectedAnd->addCondition($protectedCondition);

						$memberLoginCondition = new MemberLoginCondition('logged_out');
						$unprotectedAnd->addCondition($memberLoginCondition);

						$articleGuestsCondition = new ArticleGuestsCondition(false);
						$unprotectedAnd->addCondition($articleGuestsCondition);
					}

					$protectedAnd = new AndCondition();
					$loginStatusOr->addCondition($protectedAnd);

					{
						$protectedCondition = new ArticleProtectedCondition(true);
						$protectedAnd->addCondition($protectedCondition);

						$articleGroupsCondition = new ArticleGroupsCondition();
						$protectedAnd->addCondition($articleGroupsCondition);
					}
				}
			}

			{
				$and = new AndCondition();
				$or->addCondition($and);

				$typeCondition = new TypeCondition('content');
				$and->addCondition($typeCondition);

				$publishedCondition = new ContentPublishedCondition();
				$and->addCondition($publishedCondition);

				$loginStatusOr = new OrCondition();
				$and->addCondition($loginStatusOr);

				{
					$unprotectedAnd = new AndCondition();
					$loginStatusOr->addCondition($unprotectedAnd);

					{
						$protectedCondition = new ContentProtectedCondition(false);
						$unprotectedAnd->addCondition($protectedCondition);

						$memberLoginCondition = new MemberLoginCondition('logged_out');
						$unprotectedAnd->addCondition($memberLoginCondition);

						$articleGuestsCondition = new ContentGuestsCondition(false);
						$unprotectedAnd->addCondition($articleGuestsCondition);
					}

					$protectedAnd = new AndCondition();
					$loginStatusOr->addCondition($protectedAnd);

					{
						$protectedCondition = new ContentProtectedCondition(true);
						$protectedAnd->addCondition($protectedCondition);

						$articleGroupsCondition = new ContentGroupsCondition();
						$protectedAnd->addCondition($articleGroupsCondition);
					}
				}
			}
		}

		if ($this->toc_max_level < 6) {
			$max = $this->toc_max_level;

			if ($this->toc_include_articles) {
				$max += 1;
			}

			$root->addCondition(new LevelCondition($max));
		}

		return $root;
	}

	public function createLinkCondition()
	{
		if ($this->toc_min_level > 1) {
			$min = $this->toc_min_level;

			$or = new OrCondition();

			if ($this->toc_include_articles) {
				$or->addCondition(new LevelCondition(1));
				$min += 1;
			}

			$or->addCondition(new LevelCondition(7, $min));

			return $or;
		}

		return null;
	}
}
