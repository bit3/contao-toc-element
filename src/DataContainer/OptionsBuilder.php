<?php

/*
 * This file is part of the "Table of contents element" package.
 *
 * (c) Tristan Lins <tristan.lins@bit3.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bit3\Contao\TableOfContentsElement\DataContainer;

use ContaoCommunityAlliance\Contao\Events\CreateOptions\CreateOptionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Bit3\Contao\TableOfContentsElement\TableOfContentsElementEvents;

class OptionsBuilder implements EventSubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
			TableOfContentsElementEvents::CREATE_SECTION_OPTIONS => 'getSectionOptions',
			TableOfContentsElementEvents::CREATE_ARTICLE_OPTIONS => 'getArticleOptions'
		);
	}

	public function getSectionOptions(CreateOptionsEvent $event)
	{
		\Controller::loadLanguageFile('tl_article');

		$database = \Database::getInstance();
		$options  = $event->getOptions();

		// add columns to options array
		foreach (array('header', 'left', 'main', 'right', 'footer') as $sectionName) {
			$options[$sectionName] = $GLOBALS['TL_LANG']['tl_article'][$sectionName];
		}

		// add custom sections (columns) from all layouts
		$resultSet = $database
			->prepare('SELECT * FROM tl_layout WHERE sections!=?')
			->execute('');
		while ($resultSet->next()) {
			$customSections = trimsplit(',', $resultSet->sections);
			$customSections = array_filter($customSections);

			foreach ($customSections as $sectionName) {
				if (!isset($options[$sectionName])) {
					$options[$sectionName] = isset($GLOBALS['TL_LANG']['tl_article'][$sectionName])
						? $GLOBALS['TL_LANG']['tl_article'][$sectionName]
						: $sectionName;
				}
			}
		}
	}

	public function getArticleOptions(CreateOptionsEvent $event)
	{
		\Controller::loadLanguageFile('tl_article');

		$database      = \Database::getInstance();
		$dataContainer = $event->getDataContainer();
		$options       = $event->getOptions();

		// add articles in this page to options array
		$resultSet = $database
			->prepare(
				'SELECT a.id, a.title, a.inColumn
				 FROM tl_article a
				 INNER JOIN tl_article b
				 ON a.pid = b.pid
				 INNER JOIN tl_content c
				 ON c.pid = b.id
				 WHERE c.id = ?
				 ORDER BY a.inColumn, a.sorting'
			)
			->execute($dataContainer->id);
		while ($resultSet->next()) {
			if (isset($GLOBALS['TL_LANG']['tl_article'][$resultSet->inColumn])) {
				$sectionName = $GLOBALS['TL_LANG']['tl_article'][$resultSet->inColumn];
			}
			else {
				$sectionName = $resultSet->inColumn;
			}
			if (isset($GLOBALS['TL_LANG']['tl_article'][$sectionName])) {
				$sectionName = $GLOBALS['TL_LANG']['tl_article'][$sectionName];
			}
			$options[$sectionName][$resultSet->id] = $resultSet->title;
		}
	}
}
