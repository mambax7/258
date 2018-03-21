<?php
/**
 * extCal module.
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright ::  XOOPS Project (https://xoops.org)
 * @license   ::    http://www.fsf.org/copyleft/gpl.html GNU public license
 * @subpackage:: admin
 *
 * @since     :       2.5.0
 *
 * @author    Mamba (www.xoops.org)
 **/

$pathIcon32 = \Xmf\Module\Admin::iconUrl('', 32);
echo "<div class='adminfooter'>\n" . "  <div style='text-align: center;'>\n" . "    <a href='https://xoops.org' rel='external'><img src='{$pathIcon32}/xoopsmicrobutton.gif' alt='XOOPS' title='XOOPS'></a>\n" . "  </div>\n" . '  ' . _AM_MODULEADMIN_ADMIN_FOOTER . "\n" . '</div>';

xoops_cp_footer();
