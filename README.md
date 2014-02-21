Responsive Multi Level Menu
===========================

Responsive Multi Level Menu is a Joomla 2.5/3.x module to display a responsive multi level menu.
This module is based on the original work of Mary Lou (Manoela Ilic) at http://tympanus.net/codrops/2013/04/19/responsive-multi-level-menu/

Use the file **mod_resp_mlm_installer.zip** to install on your Joomla 3.x website.

The module has the defaule menu parameters which are the same as the original Menu Module.

Additionally, you can set the following parameters:

- **Back text**: the text to appear on the "back" menu item when navigating into submenu's.
- **Color Set**: choose one of the 5 predefined color sets. Or choose "Use your own".
- **Color1**: when you choose "Use your own" as color set, choose your first color here. This color is used as the button color.
- **Color2**: when you choose "Use your own" as color set, choose your second color here. This color is used as the button hover color.
- **Animation**: choose one of the five animations.
- **Load jQuery**: specify if the module should load jQuery (from the Google API's). If your template already loads jQuery, set this parameter to "No".

Update version 0.2

The javascript code prevents a menu item that has subitems to be executed.
When clicked on such a parent item, the javascript displays the submenu and doesn't execute the link connected to the parent item.
To fix this, every parent item that has a submenu is cloned into the submenu as the first item.