<?php

/*
 * This file is part of the "Table of contents element" package.
 *
 * (c) Tristan Lins <tristan.lins@bit3.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Event subscribers
 */
$GLOBALS['TL_EVENT_SUBSCRIBERS'][] = 'Bit3\Contao\TableOfContentsElement\DataContainer\OptionsBuilder';

/**
 * Content elements
 */
$GLOBALS['TL_CTE']['links']['table-of-contents'] = 'Bit3\Contao\TableOfContentsElement\ContentElement\TableOfContentsElement';
