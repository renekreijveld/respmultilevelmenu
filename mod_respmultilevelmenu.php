<?php
/**
 * Responsive Multi Level Menu
 * Joomla 3.2.x module based on the work of Mary Lou (Manoela Ilic), http://tympanus.net/codrops/2013/04/19/responsive-multi-level-menu/
 * Code based on the original Joomla Menu Module
 *
 * Copyright (C) 4 ReneKreijveld.nl, http://about.me/renekreijveld
 * GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$list		= ModRespMultiLevelMenuHelper::getList($params);
$base		= ModRespMultiLevelMenuHelper::getBase($params);
$active		= ModRespMultiLevelMenuHelper::getActive($params);
$active_id 	= $active->id;
$path		= $base->tree;

$showAll	= $params->get('showAllChildren');
$class_sfx	= htmlspecialchars($params->get('class_sfx'));
$script		= ModRespMultiLevelMenuHelper::getScript($params);
$animation	= htmlspecialchars($params->get('Animation'));

if (count($list))
{
	require JModuleHelper::getLayoutPath('mod_respmultilevelmenu', $params->get('layout', 'default'));
}