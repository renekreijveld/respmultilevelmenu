<?php
/**
 * Responsive Multi Level Menu - helper file
 * Joomla 3.2.x module based on the work of Mary Lou, http://tympanus.net/codrops/2013/04/19/responsive-multi-level-menu/
 * Code based on the original Joomla Menu Module
 *
 * Copyright (C) 4 ReneKreijveld.nl, http://about.me/renekreijveld
 * GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$loadjquery	= htmlspecialchars($params->get('loadjQuery'));
$colorset = htmlspecialchars($params->get('colorSet'));
$color1 = htmlspecialchars($params->get('color1'));
$color2 = htmlspecialchars($params->get('color2'));
switch ($colorset) {
	case "1":
		$css = ".dl-menuwrapper button {background:#c62860;}.dl-menuwrapper button:hover,.dl-menuwrapper button.dl-active,.dl-menuwrapper ul {background:#9e1847;}";
		break;
	case "2":
		$css = ".dl-menuwrapper button {background:#e86814;}.dl-menuwrapper button:hover,.dl-menuwrapper button.dl-active,.dl-menuwrapper ul {background:#D35400;}";
		break;
	case "3":
		$css = ".dl-menuwrapper button {background:#08cbc4;}.dl-menuwrapper button:hover,.dl-menuwrapper button.dl-active,.dl-menuwrapper ul {background:#00b4ae;}";
		break;
	case "4":
		$css = ".dl-menuwrapper button {background:#90b912;}.dl-menuwrapper button:hover,.dl-menuwrapper button.dl-active,.dl-menuwrapper ul {background:#79a002;}";
		break;
	case "5":
		$css = ".dl-menuwrapper button{background:#744783;}.dl-menuwrapper button:hover,.dl-menuwrapper button.dl-active,.dl-menuwrapper ul {background:#643771;}";
		break;
	case "0":
		$css = ".dl-menuwrapper button{background:".$color1.";}.dl-menuwrapper button:hover,.dl-menuwrapper button.dl-active,.dl-menuwrapper ul {background:".$color2.";}";
		break;
}
$document =& JFactory::getDocument();
JHtml::stylesheet('modules/mod_respmultilevelmenu/assets/respmultilevelmenu.css');
$document->addStyleDeclaration($css);
$document->addScript('modules/mod_respmultilevelmenu/assets/modernizr.custom.js');
if ($loadjquery==1) $document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');

/**
 * Helper for mod_menu
 *
 * @package     Joomla.Site
 * @subpackage  mod_menu
 * @since       1.5
 */
class ModRespMultiLevelMenuHelper
{
	/**
	 * Get a list of the menu items.
	 *
	 * @param   JRegistry  &$params  The module options.
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public static function getList(&$params)
	{
		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		// Get active menu item
		$base = self::getBase($params);
		$user = JFactory::getUser();
		$levels = $user->getAuthorisedViewLevels();
		asort($levels);
		$key = 'menu_items' . $params . implode(',', $levels) . '.' . $base->id;
		$cache = JFactory::getCache('mod_menu', '');

		if (!($items = $cache->get($key)))
		{
			$path    = $base->tree;
			$start   = (int) $params->get('startLevel');
			$end     = (int) $params->get('endLevel');
			$showAll = $params->get('showAllChildren');
			$items   = $menu->getItems('menutype', $params->get('menutype'));

			$lastitem = 0;

			if ($items)
			{
				foreach ($items as $i => $item)
				{
					if (($start && $start > $item->level)
						|| ($end && $item->level > $end)
						|| (!$showAll && $item->level > 1 && !in_array($item->parent_id, $path))
						|| ($start > 1 && !in_array($item->tree[$start - 2], $path)))
					{
						unset($items[$i]);
						continue;
					}

					$item->deeper     = false;
					$item->shallower  = false;
					$item->level_diff = 0;

					if (isset($items[$lastitem]))
					{
						$items[$lastitem]->deeper     = ($item->level > $items[$lastitem]->level);
						$items[$lastitem]->shallower  = ($item->level < $items[$lastitem]->level);
						$items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
					}

					$item->parent = (boolean) $menu->getItems('parent_id', (int) $item->id, true);

					$lastitem     = $i;
					$item->active = false;
					$item->flink  = $item->link;

					// Reverted back for CMS version 2.5.6
					switch ($item->type)
					{
						case 'separator':
						case 'heading':
							// No further action needed.
							continue;

						case 'url':
							if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
							{
								// If this is an internal Joomla link, ensure the Itemid is set.
								$item->flink = $item->link . '&Itemid=' . $item->id;
							}
							break;

						case 'alias':
							// If this is an alias use the item id stored in the parameters to make the link.
							$item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
							break;

						default:
							$router = $app::getRouter();

							if ($router->getMode() == JROUTER_MODE_SEF)
							{
								$item->flink = 'index.php?Itemid=' . $item->id;
							}
							else
							{
								$item->flink .= '&Itemid=' . $item->id;
							}
							break;
					}

					if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false))
					{
						$item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
					}
					else
					{
						$item->flink = JRoute::_($item->flink);
					}

					// We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
					// when the cause of that is found the argument should be removed
					$item->title        = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
					$item->anchor_css   = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
					$item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
					$item->menu_image   = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
				}

				if (isset($items[$lastitem]))
				{
					$items[$lastitem]->deeper     = (($start?$start:1) > $items[$lastitem]->level);
					$items[$lastitem]->shallower  = (($start?$start:1) < $items[$lastitem]->level);
					$items[$lastitem]->level_diff = ($items[$lastitem]->level - ($start?$start:1));
				}
			}

			$cache->store($items, $key);
		}

		return $items;
	}

	/**
	 * Get base menu item.
	 *
	 * @param   JRegistry  &$params  The module options.
	 *
	 * @return   object
	 *
	 * @since	3.0.2
	 */
	public static function getBase(&$params)
	{
		// Get base menu item from parameters
		if ($params->get('base'))
		{
			$base = JFactory::getApplication()->getMenu()->getItem($params->get('base'));
		}
		else
		{
			$base = false;
		}

		// Use active menu item if no base found
		if (!$base)
		{
			$base = self::getActive($params);
		}

		return $base;
	}

	/**
	 * Get active menu item.
	 *
	 * @param   JRegistry  &$params  The module options.
	 *
	 * @return  object
	 *
	 * @since	3.0.2
	 */
	public static function getActive(&$params)
	{
		$menu = JFactory::getApplication()->getMenu();

		return $menu->getActive() ? $menu->getActive() : $menu->getDefault();
	}

	/**
	 * Returns the dlmenu script
	 *
	 * @param   JRegistry  &$params  The module options.
	 *
	 * @return  object
	 */
	public static function getScript(&$params)
	{
		$script = '(function(e,t,n){"use strict";var r=t.Modernizr,i=e("body");e.DLMenu=function(t,n){this.$el=e(n);this._init(t)};e.DLMenu.defaults={animationClasses:{classin:"dl-animate-in-1",classout:"dl-animate-out-1"},onLevelClick:function(e,t){return!1},onLinkClick:function(e,t){return!1}};e.DLMenu.prototype={_init:function(t){this.options=e.extend(!0,{},e.DLMenu.defaults,t);this._config();var n={WebkitAnimation:"webkitAnimationEnd",OAnimation:"oAnimationEnd",msAnimation:"MSAnimationEnd",animation:"animationend"},i={WebkitTransition:"webkitTransitionEnd",MozTransition:"transitionend",OTransition:"oTransitionEnd",msTransition:"MSTransitionEnd",transition:"transitionend"};this.animEndEventName=n[r.prefixed("animation")]+".dlmenu";this.transEndEventName=i[r.prefixed("transition")]+".dlmenu",this.supportAnimations=r.cssanimations,this.supportTransitions=r.csstransitions;this._initEvents()},_config:function(){this.open=!1;this.$trigger=this.$el.children(".dl-trigger");this.$menu=this.$el.children("ul.dl-menu");this.$menuitems=this.$menu.find("li:not(.dl-back)");this.$el.find("ul.dl-submenu").prepend(\'<li class="dl-back"><a href="#">#backtext#</a></li>\');this.$back=this.$menu.find("li.dl-back")},_initEvents:function(){var t=this;this.$trigger.on("click.dlmenu",function(){t.open?t._closeMenu():t._openMenu();return!1});this.$menuitems.on("click.dlmenu",function(n){n.stopPropagation();var r=e(this),i=r.children("ul.dl-submenu");if(i.length>0){var s=i.clone().css("opacity",0).insertAfter(t.$menu),o=function(){t.$menu.off(t.animEndEventName).removeClass(t.options.animationClasses.classout).addClass("dl-subview");r.addClass("dl-subviewopen").parents(".dl-subviewopen:first").removeClass("dl-subviewopen").addClass("dl-subview");s.remove()};setTimeout(function(){s.addClass(t.options.animationClasses.classin);t.$menu.addClass(t.options.animationClasses.classout);t.supportAnimations?t.$menu.on(t.animEndEventName,o):o.call();t.options.onLevelClick(r,r.children("a:first").text())});return!1}t.options.onLinkClick(r,n)});this.$back.on("click.dlmenu",function(n){var r=e(this),i=r.parents("ul.dl-submenu:first"),s=i.parent(),o=i.clone().insertAfter(t.$menu),u=function(){t.$menu.off(t.animEndEventName).removeClass(t.options.animationClasses.classin);o.remove()};setTimeout(function(){o.addClass(t.options.animationClasses.classout);t.$menu.addClass(t.options.animationClasses.classin);t.supportAnimations?t.$menu.on(t.animEndEventName,u):u.call();s.removeClass("dl-subviewopen");var e=r.parents(".dl-subview:first");e.is("li")&&e.addClass("dl-subviewopen");e.removeClass("dl-subview")});return!1})},closeMenu:function(){this.open&&this._closeMenu()},_closeMenu:function(){var e=this,t=function(){e.$menu.off(e.transEndEventName);e._resetMenu()};this.$menu.removeClass("dl-menuopen");this.$menu.addClass("dl-menu-toggle");this.$trigger.removeClass("dl-active");this.supportTransitions?this.$menu.on(this.transEndEventName,t):t.call();this.open=!1},openMenu:function(){this.open||this._openMenu()},_openMenu:function(){var t=this;i.off("click").on("click.dlmenu",function(){t._closeMenu()});this.$menu.addClass("dl-menuopen dl-menu-toggle").on(this.transEndEventName,function(){e(this).removeClass("dl-menu-toggle")});this.$trigger.addClass("dl-active");this.open=!0},_resetMenu:function(){this.$menu.removeClass("dl-subview");this.$menuitems.removeClass("dl-subview dl-subviewopen")}};var s=function(e){t.console&&t.console.error(e)};e.fn.dlmenu=function(t){if(typeof t=="string"){var n=Array.prototype.slice.call(arguments,1);this.each(function(){var r=e.data(this,"dlmenu");if(!r){s("cannot call methods on dlmenu prior to initialization; attempted to call method \'"+t+"\'");return}if(!e.isFunction(r[t])||t.charAt(0)==="_"){s("no such method \'"+t+"\' for dlmenu instance");return}r[t].apply(r,n)})}else this.each(function(){var n=e.data(this,"dlmenu");n?n._init():n=e.data(this,"dlmenu",new e.DLMenu(t,this))});return this}})(jQuery,window);';
		$backtext = htmlspecialchars($params->get('backText'));

		$script = str_replace('#backtext#',$backtext,$script);
		return $script;
	}
}
