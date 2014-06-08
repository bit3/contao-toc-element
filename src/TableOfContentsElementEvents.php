<?php

/*
 * This file is part of the "Table of contents element" package.
 *
 * (c) Tristan Lins <tristan.lins@bit3.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bit3\Contao\TableOfContentsElement;

use ContaoCommunityAlliance\Contao\Events\CreateOptions\CreateOptionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TableOfContentsElementEvents
{
	const CREATE_SECTION_OPTIONS = 'toc-element.create-section-options';
	const CREATE_ARTICLE_OPTIONS = 'toc-element.create-article-options';
}
