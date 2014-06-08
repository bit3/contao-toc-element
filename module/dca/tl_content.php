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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['metapalettes']['table-of-contents'] = array(
	'type'       => array('type', 'headline'),
	'navigation' => array('toc_source', 'toc_include_articles', 'toc_min_level', 'toc_max_level', 'xnavigation_template'),
	'protected'  => array(':hide', 'protected'),
	'expert'     => array(':hide', 'guests', 'cssID', 'space'),
	'invisible'  => array(':hide', 'invisible', 'start', 'stop'),
);

$GLOBALS['TL_DCA']['tl_content']['metasubselectpalettes']['toc_source'] = array(
	'sections' => array('toc_sections'),
	'articles' => array('toc_articles'),
);

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['toc_source'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_content']['toc_source'],
	'default'   => 'sections',
	'inputType' => 'select',
	'options'   => array('sections', 'articles'),
	'reference' => &$GLOBALS['TL_LANG']['tl_content']['toc_sources'],
	'eval'      => array('mandatory' => true, 'submitOnChange' => true),
	'sql'       => 'varchar(10) NOT NULL default \'\'',
);

$GLOBALS['TL_DCA']['tl_content']['fields']['toc_sections'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_content']['toc_sections'],
	'default'          => 'main',
	'inputType'        => 'checkboxWizard',
	'options_callback' => \ContaoCommunityAlliance\Contao\Events\CreateOptions\CreateOptionsEventCallbackFactory::createCallback(
		\Bit3\Contao\TableOfContentsElement\TableOfContentsElementEvents::CREATE_SECTION_OPTIONS
	),
	'eval'             => array('mandatory' => true, 'multiple' => true),
	'sql'              => 'text NULL',
);

$GLOBALS['TL_DCA']['tl_content']['fields']['toc_articles'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_content']['toc_articles'],
	'default'          => 'main',
	'inputType'        => 'checkbox',
	'options_callback' => \ContaoCommunityAlliance\Contao\Events\CreateOptions\CreateOptionsEventCallbackFactory::createCallback(
		\Bit3\Contao\TableOfContentsElement\TableOfContentsElementEvents::CREATE_ARTICLE_OPTIONS
	),
	'eval'             => array('mandatory' => true, 'multiple' => true),
	'sql'              => 'text NULL',
);

$GLOBALS['TL_DCA']['tl_content']['fields']['toc_include_articles'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_content']['toc_include_articles'],
	'inputType'        => 'checkbox',
	'eval'             => array('tl_class' => 'm12'),
	'sql'              => 'char(1) NOT NULL default \'\'',
);

$GLOBALS['TL_DCA']['tl_content']['fields']['toc_min_level'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_content']['toc_min_level'],
	'default'   => '1',
	'inputType' => 'select',
	'options'   => array('1', '2', '3', '4', '5', '6'),
	'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
	'sql'       => 'tinyint(1) NOT NULL default \'1\''
);

$GLOBALS['TL_DCA']['tl_content']['fields']['toc_max_level'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_content']['toc_max_level'],
	'default'   => '6',
	'inputType' => 'select',
	'options'   => array('1', '2', '3', '4', '5', '6'),
	'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
	'sql'       => 'tinyint(1) NOT NULL default \'6\'',
);
