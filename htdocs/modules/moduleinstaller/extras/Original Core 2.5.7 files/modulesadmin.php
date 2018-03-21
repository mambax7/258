<?php
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    XOOPS Project https://xoops.org/
 * @license      GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package
 * @since
 * @author       XOOPS Development Team, Kazumi Ono (AKA onokazu)
 */

/*
if ( !is_object($xoopsUser) || !is_object($xoopsModule) || !$xoopsUser->isAdmin($xoopsModule->mid()) ) {
    exit("Access Denied");
}
*/

/**
 * @param $dirname
 *
 * @return string
 */
function xoops_module_install($dirname)
{
    global $xoopsUser, $xoopsConfig;
    $dirname = trim($dirname);
    //    $db = $GLOBALS['xoopsDB'];
    $db             = \XoopsDatabaseFactory::getDatabaseConnection();
    $reservedTables = [
        'avatar',
        'avatar_users_link',
        'block_module_link',
        'xoopscomments',
        'config',
        'configcategory',
        'configoption',
        'image',
        'imagebody',
        'imagecategory',
        'imgset',
        'imgset_tplset_link',
        'imgsetimg',
        'groups',
        'groups_users_link',
        'group_permission',
        'online',
        'bannerclient',
        'banner',
        'bannerfinish',
        'priv_msgs',
        'ranks',
        'session',
        'smiles',
        'users',
        'newblocks',
        'modules',
        'tplfile',
        'tplset',
        'tplsource',
        'xoopsnotifications',
        'banner',
        'bannerclient',
        'bannerfinish'
    ];
    /** @var XoopsModuleHandler $moduleHandler */
    $moduleHandler = xoops_getHandler('module');
    if (0 == $moduleHandler->getCount(new \Criteria('dirname', $dirname))) {
        $module = $moduleHandler->create();
        $module->loadInfoAsVar($dirname);
        $module->setVar('weight', 1);
        $module->setVar('isactive', 1);
        $module->setVar('last_update', time());
        $error = false;
        $errs  = [];
        $msgs  = [];

        $msgs[] = '<div id="xo-module-log"><div class="header">';
        $msgs[] = $errs[] = '<h4>' . _AM_SYSTEM_MODULES_INSTALLING . $module->getInfo('name', 's') . '</h4>';
        if (false !== $module->getInfo('image') && '' != trim($module->getInfo('image'))) {
            $msgs[] = '<a href="' . XOOPS_URL . '/modules/' . $module->getInfo('dirname', 'e') . '/' . $module->getInfo('adminindex') . '"><img src="' . XOOPS_URL . '/modules/' . $dirname . '/' . trim($module->getInfo('image')) . '" alt=""></a>';
        }
        $msgs[] = '<strong>' . _VERSION . ':</strong> ' . $module->getInfo('version') . '&nbsp;' . $module->getInfo('module_status');
        if (false !== $module->getInfo('author') && '' != trim($module->getInfo('author'))) {
            $msgs[] = '<strong>' . _AUTHOR . ':</strong> ' . htmlspecialchars(trim($module->getInfo('author')));
        }
        $msgs[] = '</div><div class="logger">';
        // Load module specific install script if any
        $install_script = $module->getInfo('onInstall');
        if ($install_script && '' != trim($install_script)) {
            require_once XOOPS_ROOT_PATH . '/modules/' . $dirname . '/' . trim($install_script);
        }
        $func = "xoops_module_pre_install_{$dirname}";
        // If pre install function is defined, execute
        if (function_exists($func)) {
            $result = $func($module);
            if (!$result) {
                $error  = true;
                $errs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILED_EXECUTE, $func) . '</p>';
                $errs   = array_merge($errs, $module->getErrors());
            } else {
                $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILED_SUCESS, "<strong>{$func}</strong>") . '</p>';
                $msgs   += $module->getErrors();
            }
        }

        if (false === $error) {
            $sqlfile = $module->getInfo('sqlfile');
            if (is_array($sqlfile) && !empty($sqlfile[XOOPS_DB_TYPE])) {
                $sql_file_path = XOOPS_ROOT_PATH . '/modules/' . $dirname . '/' . $sqlfile[XOOPS_DB_TYPE];
                if (!file_exists($sql_file_path)) {
                    $errs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_SQL_NOT_FOUND, "<strong>{$sql_file_path}</strong>");
                    $error  = true;
                } else {
                    $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_SQL_FOUND, "<strong>{$sql_file_path}</strong>") . '<br >' . _AM_SYSTEM_MODULES_CREATE_TABLES;
                    require_once XOOPS_ROOT_PATH . '/class/database/sqlutility.php';
                    $sql_query = fread(fopen($sql_file_path, 'r'), filesize($sql_file_path));
                    $sql_query = trim($sql_query);
                    SqlUtility::splitMySqlFile($pieces, $sql_query);
                    $created_tables = [];
                    foreach ($pieces as $piece) {
                        // [0] contains the prefixed query
                        // [4] contains unprefixed table name
                        $prefixed_query = SqlUtility::prefixQuery($piece, $db->prefix());
                        if (!$prefixed_query) {
                            $errs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_SQL_NOT_VALID, '<strong>' . $piece . '</strong>');
                            $error  = true;
                            break;
                        }
                        // check if the table name is reserved
                        if (!in_array($prefixed_query[4], $reservedTables)) {
                            // not reserved, so try to create one
                            if (!$db->query($prefixed_query[0])) {
                                $errs[] = $db->error();
                                $error  = true;
                                break;
                            } else {
                                if (!in_array($prefixed_query[4], $created_tables)) {
                                    $msgs[]           = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_TABLE_CREATED, '<strong>' . $db->prefix($prefixed_query[4]) . '</strong>');
                                    $created_tables[] = $prefixed_query[4];
                                } else {
                                    $msgs[] = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_INSERT_DATA, '<strong>' . $db->prefix($prefixed_query[4]) . '</strong>');
                                }
                            }
                        } else {
                            // the table name is reserved, so halt the installation
                            $errs[] = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_TABLE_RESERVED, '<strong>' . $prefixed_query[4] . '</strong>');
                            $error  = true;
                            break;
                        }
                    }
                    // if there was an error, delete the tables created so far, so the next installation will not fail
                    if (true === $error) {
                        foreach ($created_tables as $ct) {
                            $db->query('DROP TABLE ' . $db->prefix($ct));
                        }
                    }
                }
            }
        }
        // if no error, save the module info and blocks info associated with it
        if (false === $error) {
            if (!$moduleHandler->insert($module)) {
                $errs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_INSERT_DATA_FAILD, '<strong>' . $module->getVar('name') . '</strong>');
                foreach ($created_tables as $ct) {
                    $db->query('DROP TABLE ' . $db->prefix($ct));
                }
                $ret = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILINS, '<strong>' . $module->name() . '</strong>') . '&nbsp;' . _AM_SYSTEM_MODULES_ERRORSC . '<br>';
                foreach ($errs as $err) {
                    $ret .= ' - ' . $err . '<br>';
                }
                $ret .= '</p>';
                unset($module);
                unset($created_tables);
                unset($errs);
                unset($msgs);

                return $ret;
            } else {
                $newmid = $module->getVar('mid');
                unset($created_tables);
                $msgs[]         = '<p>' . _AM_SYSTEM_MODULES_INSERT_DATA_DONE . sprintf(_AM_SYSTEM_MODULES_MODULEID, '<strong>' . $newmid . '</strong>');
                $tplfileHandler = xoops_getHandler('tplfile');
                $templates      = $module->getInfo('templates');
                if (false !== $templates) {
                    $msgs[] = _AM_SYSTEM_MODULES_TEMPLATES_ADD;
                    foreach ($templates as $tpl) {
                        $tplfile = $tplfileHandler->create();
                        $type    = (isset($tpl['type']) ? $tpl['type'] : 'module');
                        $tpldata = xoops_module_gettemplate($dirname, $tpl['file'], $type);
                        $tplfile->setVar('tpl_source', $tpldata, true);
                        $tplfile->setVar('tpl_refid', $newmid);

                        $tplfile->setVar('tpl_tplset', 'default');
                        $tplfile->setVar('tpl_file', $tpl['file']);
                        $tplfile->setVar('tpl_desc', $tpl['description'], true);
                        $tplfile->setVar('tpl_module', $dirname);
                        $tplfile->setVar('tpl_lastmodified', time());
                        $tplfile->setVar('tpl_lastimported', time());
                        $tplfile->setVar('tpl_type', $type);
                        if (!$tplfileHandler->insert($tplfile)) {
                            $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_ADD_ERROR, '<strong>' . $tpl['file'] . '</strong>') . '</span>';
                        } else {
                            $newtplid = $tplfile->getVar('tpl_id');
                            $msgs[]   = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_ADD_DATA, '<strong>' . $tpl['file'] . '</strong>') . '(ID: <strong>' . $newtplid . '</strong>)';
                            // generate compiled file
                            require_once XOOPS_ROOT_PATH . '/class/template.php';
                            if (!xoops_template_touch($newtplid)) {
                                $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_COMPILED_FAILED, '<strong>' . $tpl['file'] . '</strong>') . '</span>';
                            } else {
                                $msgs[] = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_COMPILED, '<strong>' . $tpl['file'] . '</strong>');
                            }
                        }
                        unset($tplfile, $tpldata);
                    }
                }
                require_once XOOPS_ROOT_PATH . '/class/template.php';
                xoops_template_clear_module_cache($newmid);
                $blocks = $module->getInfo('blocks');
                if (false !== $blocks) {
                    $msgs[] = _AM_SYSTEM_MODULES_BLOCKS_ADD;
                    foreach ($blocks as $blockkey => $block) {
                        // break the loop if missing block config
                        if (!isset($block['file']) || !isset($block['show_func'])) {
                            break;
                        }
                        $options = '';
                        if (!empty($block['options'])) {
                            $options = trim($block['options']);
                        }
                        $newbid    = $db->genId($db->prefix('newblocks') . '_bid_seq');
                        $edit_func = isset($block['edit_func']) ? trim($block['edit_func']) : '';
                        $template  = '';
                        if (isset($block['template']) && '' != trim($block['template'])) {
                            $content = xoops_module_gettemplate($dirname, $block['template'], 'blocks');
                        }
                        if (empty($content)) {
                            $content = '';
                        } else {
                            $template = trim($block['template']);
                        }
                        $block_name = addslashes(trim($block['name']));
                        $sql        = 'INSERT INTO '
                                      . $db->prefix('newblocks')
                                      . " (bid, mid, func_num, options, name, title, content, side, weight, visible, block_type, c_type, isactive, dirname, func_file, show_func, edit_func, template, bcachetime, last_modified) VALUES ($newbid, $newmid, "
                                      . (int)$blockkey
                                      . ", '$options', '"
                                      . $block_name
                                      . "','"
                                      . $block_name
                                      . "', '', 0, 0, 0, 'M', 'H', 1, '"
                                      . addslashes($dirname)
                                      . "', '"
                                      . addslashes(trim($block['file']))
                                      . "', '"
                                      . addslashes(trim($block['show_func']))
                                      . "', '"
                                      . addslashes($edit_func)
                                      . "', '"
                                      . $template
                                      . "', 0, "
                                      . time()
                                      . ')';
                        if (!$db->query($sql)) {
                            $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_BLOCK_ADD_ERROR, '<strong>' . $block['name'] . '</strong>') . sprintf(_AM_SYSTEM_MODULES_BLOCK_ADD_ERROR_DATABASE, '<strong>' . $db->error() . '</strong>') . '</span>';
                        } else {
                            if (empty($newbid)) {
                                $newbid = $db->getInsertId();
                            }
                            $msgs[] = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_BLOCK_ADD, '<strong>' . $block['name'] . '</strong>') . sprintf(_AM_SYSTEM_MODULES_BLOCK_ID, '<strong>' . $newbid . '</strong>');
                            $sql    = 'INSERT INTO ' . $db->prefix('block_module_link') . ' (block_id, module_id) VALUES (' . $newbid . ', -1)';
                            $db->query($sql);
                            if ('' != $template) {
                                $tplfile = $tplfileHandler->create();
                                $tplfile->setVar('tpl_refid', $newbid);
                                $tplfile->setVar('tpl_source', $content, true);
                                $tplfile->setVar('tpl_tplset', 'default');
                                $tplfile->setVar('tpl_file', $block['template']);
                                $tplfile->setVar('tpl_module', $dirname);
                                $tplfile->setVar('tpl_type', 'block');
                                $tplfile->setVar('tpl_desc', $block['description'], true);
                                $tplfile->setVar('tpl_lastimported', 0);
                                $tplfile->setVar('tpl_lastmodified', time());
                                if (!$tplfileHandler->insert($tplfile)) {
                                    $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_ADD_ERROR, '<strong>' . $block['template'] . '</strong>') . '</span>';
                                } else {
                                    $newtplid = $tplfile->getVar('tpl_id');
                                    $msgs[]   = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_ADD_DATA, '<strong>' . $block['template'] . '</strong>') . ' (ID: <strong>' . $newtplid . '</strong>)';
                                    // generate compiled file
                                    require_once XOOPS_ROOT_PATH . '/class/template.php';
                                    if (!xoops_template_touch($newtplid)) {
                                        $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_COMPILED_FAILED, '<strong>' . $block['template'] . '</strong>') . '</span>';
                                    } else {
                                        $msgs[] = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_COMPILED, '<strong>' . $block['template'] . '</strong>');
                                    }
                                }
                                unset($tplfile);
                            }
                        }
                        unset($content);
                    }
                    unset($blocks);
                }
                $configs = $module->getInfo('config');
                if (false !== $configs) {
                    if (0 != $module->getVar('hascomments')) {
                        require_once XOOPS_ROOT_PATH . '/include/comment_constants.php';
                        array_push($configs, [
                            'name'        => 'com_rule',
                            'title'       => '_CM_COMRULES',
                            'description' => '',
                            'formtype'    => 'select',
                            'valuetype'   => 'int',
                            'default'     => 1,
                            'options'     => [
                                '_CM_COMNOCOM'        => XOOPS_COMMENT_APPROVENONE,
                                '_CM_COMAPPROVEALL'   => XOOPS_COMMENT_APPROVEALL,
                                '_CM_COMAPPROVEUSER'  => XOOPS_COMMENT_APPROVEUSER,
                                '_CM_COMAPPROVEADMIN' => XOOPS_COMMENT_APPROVEADMIN
                            ]
                        ]);
                        array_push($configs, [
                            'name'        => 'com_anonpost',
                            'title'       => '_CM_COMANONPOST',
                            'description' => '',
                            'formtype'    => 'yesno',
                            'valuetype'   => 'int',
                            'default'     => 0
                        ]);
                    }
                } else {
                    if (0 != $module->getVar('hascomments')) {
                        $configs = [];
                        require_once XOOPS_ROOT_PATH . '/include/comment_constants.php';
                        $configs[] = [
                            'name'        => 'com_rule',
                            'title'       => '_CM_COMRULES',
                            'description' => '',
                            'formtype'    => 'select',
                            'valuetype'   => 'int',
                            'default'     => 1,
                            'options'     => [
                                '_CM_COMNOCOM'        => XOOPS_COMMENT_APPROVENONE,
                                '_CM_COMAPPROVEALL'   => XOOPS_COMMENT_APPROVEALL,
                                '_CM_COMAPPROVEUSER'  => XOOPS_COMMENT_APPROVEUSER,
                                '_CM_COMAPPROVEADMIN' => XOOPS_COMMENT_APPROVEADMIN
                            ]
                        ];
                        $configs[] = [
                            'name'        => 'com_anonpost',
                            'title'       => '_CM_COMANONPOST',
                            'description' => '',
                            'formtype'    => 'yesno',
                            'valuetype'   => 'int',
                            'default'     => 0
                        ];
                    }
                }
                // RMV-NOTIFY
                if (0 != $module->getVar('hasnotification')) {
                    if (empty($configs)) {
                        $configs = [];
                    }
                    // Main notification options
                    require_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
                    require_once XOOPS_ROOT_PATH . '/include/notification_functions.php';
                    $options                             = [];
                    $options['_NOT_CONFIG_DISABLE']      = XOOPS_NOTIFICATION_DISABLE;
                    $options['_NOT_CONFIG_ENABLEBLOCK']  = XOOPS_NOTIFICATION_ENABLEBLOCK;
                    $options['_NOT_CONFIG_ENABLEINLINE'] = XOOPS_NOTIFICATION_ENABLEINLINE;
                    $options['_NOT_CONFIG_ENABLEBOTH']   = XOOPS_NOTIFICATION_ENABLEBOTH;

                    $configs[] = [
                        'name'        => 'notification_enabled',
                        'title'       => '_NOT_CONFIG_ENABLE',
                        'description' => '_NOT_CONFIG_ENABLEDSC',
                        'formtype'    => 'select',
                        'valuetype'   => 'int',
                        'default'     => XOOPS_NOTIFICATION_ENABLEBOTH,
                        'options'     => $options
                    ];
                    // Event-specific notification options
                    // FIXME: doesn't work when update module... can't read back the array of options properly...  " changing to &quot;
                    $options    = [];
                    $categories = notificationCategoryInfo('', $module->getVar('mid'));
                    foreach ($categories as $category) {
                        $events = notificationEvents($category['name'], false, $module->getVar('mid'));
                        foreach ($events as $event) {
                            if (!empty($event['invisible'])) {
                                continue;
                            }
                            $option_name           = $category['title'] . ' : ' . $event['title'];
                            $option_value          = $category['name'] . '-' . $event['name'];
                            $options[$option_name] = $option_value;
                        }
                        unset($events);
                    }
                    unset($categories);
                    $configs[] = [
                        'name'        => 'notification_events',
                        'title'       => '_NOT_CONFIG_EVENTS',
                        'description' => '_NOT_CONFIG_EVENTSDSC',
                        'formtype'    => 'select_multi',
                        'valuetype'   => 'array',
                        'default'     => array_values($options),
                        'options'     => $options
                    ];
                }

                if (false !== $configs) {
                    $msgs[]        = _AM_SYSTEM_MODULES_MODULE_DATA_ADD;
                    $configHandler = xoops_getHandler('config');
                    $order         = 0;
                    foreach ($configs as $config) {
                        $confobj = $configHandler->createConfig();
                        $confobj->setVar('conf_modid', $newmid);
                        $confobj->setVar('conf_catid', 0);
                        $confobj->setVar('conf_name', $config['name']);
                        $confobj->setVar('conf_title', $config['title'], true);
                        $confobj->setVar('conf_desc', isset($config['description']) ? $config['description'] : '', true);
                        $confobj->setVar('conf_formtype', $config['formtype']);
                        $confobj->setVar('conf_valuetype', $config['valuetype']);
                        $confobj->setConfValueForInput($config['default'], true);
                        $confobj->setVar('conf_order', $order);
                        $confop_msgs = '';
                        if (isset($config['options']) && is_array($config['options'])) {
                            foreach ($config['options'] as $key => $value) {
                                $confop = $configHandler->createConfigOption();
                                $confop->setVar('confop_name', $key, true);
                                $confop->setVar('confop_value', $value, true);
                                $confobj->setConfOptions($confop);
                                $confop_msgs .= '<br>&nbsp;&nbsp;&nbsp;&nbsp; ' . _AM_SYSTEM_MODULES_CONFIG_ADD . _AM_SYSTEM_MODULES_NAME . ' <strong>' . (defined($key) ? constant($key) : $key) . '</strong> ' . _AM_SYSTEM_MODULES_VALUE . ' <strong>' . $value . '</strong> ';
                                unset($confop);
                            }
                        }
                        ++$order;
                        if (false !== $configHandler->insertConfig($confobj)) {
                            $msgs[] = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_CONFIG_DATA_ADD, '<strong>' . $config['name'] . '</strong>') . $confop_msgs;
                        } else {
                            $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_CONFIG_DATA_ADD_ERROR, '<strong>' . $config['name'] . '</strong>') . '</span>';
                        }
                        unset($confobj);
                    }
                    unset($configs);
                }
            }
            $groups = [XOOPS_GROUP_ADMIN];
            if ($module->getInfo('hasMain')) {
                $groups = [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS];
            }
            // retrieve all block ids for this module
            $blocks       = XoopsBlock::getByModule($newmid, false);
            $msgs[]       = _AM_SYSTEM_MODULES_GROUP_SETTINGS_ADD;
            $gpermHandler = xoops_getHandler('groupperm');
            foreach ($groups as $mygroup) {
                if ($gpermHandler->checkRight('module_admin', 0, $mygroup)) {
                    $mperm = $gpermHandler->create();
                    $mperm->setVar('gperm_groupid', $mygroup);
                    $mperm->setVar('gperm_itemid', $newmid);
                    $mperm->setVar('gperm_name', 'module_admin');
                    $mperm->setVar('gperm_modid', 1);
                    if (!$gpermHandler->insert($mperm)) {
                        $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_ACCESS_ADMIN_ADD_ERROR, '<strong>' . $mygroup . '</strong>') . '</span>';
                    } else {
                        $msgs[] = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_ACCESS_ADMIN_ADD, '<strong>' . $mygroup . '</strong>');
                    }
                    unset($mperm);
                }
                $mperm = $gpermHandler->create();
                $mperm->setVar('gperm_groupid', $mygroup);
                $mperm->setVar('gperm_itemid', $newmid);
                $mperm->setVar('gperm_name', 'module_read');
                $mperm->setVar('gperm_modid', 1);
                if (!$gpermHandler->insert($mperm)) {
                    $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_ACCESS_USER_ADD_ERROR, '<strong>' . $mygroup . '</strong>') . '</span>';
                } else {
                    $msgs[] = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_ACCESS_USER_ADD_ERROR, '<strong>' . $mygroup . '</strong>');
                }
                unset($mperm);
                foreach ($blocks as $blc) {
                    $bperm = $gpermHandler->create();
                    $bperm->setVar('gperm_groupid', $mygroup);
                    $bperm->setVar('gperm_itemid', $blc);
                    $bperm->setVar('gperm_name', 'block_read');
                    $bperm->setVar('gperm_modid', 1);
                    if (!$gpermHandler->insert($bperm)) {
                        $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . _AM_SYSTEM_MODULES_BLOCK_ACCESS_ERROR . ' Block ID: <strong>' . $blc . '</strong> Group ID: <strong>' . $mygroup . '</strong></span>';
                    } else {
                        $msgs[] = '&nbsp;&nbsp;' . _AM_SYSTEM_MODULES_BLOCK_ACCESS . sprintf(_AM_SYSTEM_MODULES_BLOCK_ID, '<strong>' . $blc . '</strong>') . sprintf(_AM_SYSTEM_MODULES_GROUP_ID, '<strong>' . $mygroup . '</strong>');
                    }
                    unset($bperm);
                }
            }
            unset($blocks);
            unset($groups);

            // execute module specific install script if any
            $func = "xoops_module_install_{$dirname}";
            if (function_exists($func)) {
                if (!$lastmsg = $func($module)) {
                    $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILED_EXECUTE, $func) . '</p>';
                } else {
                    $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILED_SUCESS, "<strong>{$func}</strong>") . '</p>';
                    if (is_string($lastmsg)) {
                        $msgs[] = $lastmsg;
                    }
                }
            }

            $msgs[] = sprintf(_AM_SYSTEM_MODULES_OKINS, '<strong>' . $module->getVar('name', 's') . '</strong>');
            $msgs[] = '</div></div>';

            $blocks = $module->getInfo('blocks');
            $msgs[] = '<div class="noininstall center"><a href="admin.php?fct=modulesadmin">' . _AM_SYSTEM_MODULES_BTOMADMIN . '</a> |
                        <a href="admin.php?fct=modulesadmin&op=installlist">' . _AM_SYSTEM_MODULES_TOINSTALL . '</a> | ';
            $msgs[] = '<br><span class="red bold">' . _AM_SYSTEM_MODULES_MODULE . ' ' . $module->getInfo('name') . ': </span></div>';
            if (false !== $blocks) {
                $msgs[] = '<div class="center"><a href="admin.php?fct=blocksadmin&op=list&filter=1&selgen=' . $newmid . '&selmod=-2&selgrp=-1&selvis=-1&filsave=1">' . _AM_SYSTEM_BLOCKS . '</a></div>';
            }

            $msgs[] = '<div class="noininstall center"><a href="admin.php?fct=preferences&op=showmod&mod=' . $newmid . '">' . _AM_SYSTEM_PREF . '</a>';
            $msgs[] = '<a href="' . XOOPS_URL . '/modules/' . $module->getInfo('dirname', 'e') . '/' . $module->getInfo('adminindex') . '">' . _AM_SYSTEM_MODULES_ADMIN . '</a>';

            $testdataDirectory = XOOPS_ROOT_PATH . '/modules/' . $module->getInfo('dirname', 'e') . '/testdata';
            if (file_exists($testdataDirectory)) {
                $msgs[] = '<a href="' . XOOPS_URL . '/modules/' . $module->getInfo('dirname', 'e') . '/testdata/index.php' . '">' . _AM_SYSTEM_MODULES_INSTALL_TESTDATA . '</a></div>';
            } else {
                $msgs[] = '</div>';
            }

            $ret = implode('<br>', $msgs);
            unset($blocks);
            unset($msgs);
            unset($errs);
            unset($module);

            return $ret;
        } else {
            $ret = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILINS, '<strong>' . $dirname . '</strong>') . '&nbsp;' . _AM_SYSTEM_MODULES_ERRORSC . '<br>' . implode('<br>', $errs) . '</p>';
            unset($msgs);
            unset($errs);

            return $ret;
        }
    } else {
        return '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILINS, '<strong>' . $dirname . '</strong>') . '&nbsp;' . _AM_SYSTEM_MODULES_ERRORSC . '<br>&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_ALEXISTS, $dirname) . '</p>';
    }
}

/**
 * @param        $dirname
 * @param        $template
 * @param string $type
 *
 * @return string
 */
function &xoops_module_gettemplate($dirname, $template, $type = '')
{
    global $xoopsConfig;
    $ret = '';
    switch ($type) {
        case 'blocks':
        case 'admin':
            $path = XOOPS_ROOT_PATH . '/modules/' . $dirname . '/templates/' . $type . '/' . $template;
            break;
        default:
            $path = XOOPS_ROOT_PATH . '/modules/' . $dirname . '/templates/' . $template;
            break;
    }
    if (!file_exists($path)) {
        return $ret;
    } else {
        $lines = file($path);
    }
    if (!$lines) {
        return $ret;
    }
    $count = count($lines);
    for ($i = 0; $i < $count; ++$i) {
        $ret .= str_replace("\n", "\r\n", str_replace("\r\n", "\n", $lines[$i]));
    }

    return $ret;
}

/**
 * @param $dirname
 *
 * @return string
 */
function xoops_module_uninstall($dirname)
{
    global $xoopsConfig;
    $reservedTables = [
        'avatar',
        'avatar_users_link',
        'block_module_link',
        'xoopscomments',
        'config',
        'configcategory',
        'configoption',
        'image',
        'imagebody',
        'imagecategory',
        'imgset',
        'imgset_tplset_link',
        'imgsetimg',
        'groups',
        'groups_users_link',
        'group_permission',
        'online',
        'bannerclient',
        'banner',
        'bannerfinish',
        'priv_msgs',
        'ranks',
        'session',
        'smiles',
        'users',
        'newblocks',
        'modules',
        'tplfile',
        'tplset',
        'tplsource',
        'xoopsnotifications',
        'banner',
        'bannerclient',
        'bannerfinish'
    ];
    $db             = \XoopsDatabaseFactory::getDatabaseConnection();
    /** @var XoopsModuleHandler $moduleHandler */
    $moduleHandler = xoops_getHandler('module');
    $module        = $moduleHandler->getByDirname($dirname);
    require_once XOOPS_ROOT_PATH . '/class/template.php';
    xoops_template_clear_module_cache($module->getVar('mid'));
    if ('system' === $module->getVar('dirname')) {
        return '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILUNINS, '<strong>' . $module->getVar('name') . '</strong>') . '&nbsp;' . _AM_SYSTEM_MODULES_ERRORSC . '<br> - ' . _AM_SYSTEM_MODULES_SYSNO . '</p>';
    } elseif ($module->getVar('dirname') == $xoopsConfig['startpage']) {
        return '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILUNINS, '<strong>' . $module->getVar('name') . '</strong>') . '&nbsp;' . _AM_SYSTEM_MODULES_ERRORSC . '<br> - ' . _AM_SYSTEM_MODULES_STRTNO . '</p>';
    } else {
        $msgs   = [];
        $msgs[] = '<div id="xo-module-log"><div class="header">';
        $msgs[] = $errs[] = '<h4>' . _AM_SYSTEM_MODULES_UNINSTAL . $module->getInfo('name', 's') . '</h4>';
        if (false !== $module->getInfo('image') && '' != trim($module->getInfo('image'))) {
            $msgs[] = '<img src="' . XOOPS_URL . '/modules/' . $dirname . '/' . trim($module->getInfo('image')) . '" alt="">';
        }
        $msgs[] = '<strong>' . _VERSION . ':</strong> ' . $module->getInfo('version') . '&nbsp;' . $module->getInfo('module_status');
        if (false !== $module->getInfo('author') && '' != trim($module->getInfo('author'))) {
            $msgs[] = '<strong>' . _AUTHOR . ':</strong> ' . htmlspecialchars(trim($module->getInfo('author')));
        }
        $msgs[] = '</div><div class="logger">';
        // Load module specific install script if any
        $uninstall_script = $module->getInfo('onUninstall');
        if ($uninstall_script && '' != trim($uninstall_script)) {
            require_once XOOPS_ROOT_PATH . '/modules/' . $dirname . '/' . trim($uninstall_script);
        }
        $func = "xoops_module_pre_uninstall_{$dirname}";
        // If pre uninstall function is defined, execute
        if (function_exists($func)) {
            $result = $func($module);
            if (false === $result) {
                $errs   = $module->getErrors();
                $errs[] = sprintf(_AM_SYSTEM_MODULES_FAILED_EXECUTE, $func);

                return '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILUNINS, '<strong>' . $module->getVar('name') . '</strong>') . '&nbsp;' . _AM_SYSTEM_MODULES_ERRORSC . '<br>' . implode('<br>', $errs) . '</p>';
            } else {
                $msgs = $module->getErrors();
                array_unshift($msgs, '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILED_SUCESS, "<strong>{$func}</strong>") . '</p>');
            }
        }

        if (false === $moduleHandler->delete($module)) {
            $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_DELETE_ERROR, $module->getVar('name')) . '</span>';
        } else {

            // delete template files
            $tplfileHandler = xoops_getHandler('tplfile');
            $templates      = $tplfileHandler->find(null, 'module', $module->getVar('mid'));
            $tcount         = count($templates);
            if ($tcount > 0) {
                $msgs[] = _AM_SYSTEM_MODULES_TEMPLATES_DELETE;
                for ($i = 0; $i < $tcount; ++$i) {
                    if (false === $tplfileHandler->delete($templates[$i])) {
                        $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_DELETE_DATA_FAILD, $templates[$i]->getVar('tpl_file')) . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_ID, '<strong>' . $templates[$i]->getVar('tpl_id') . '</strong>') . '</span>';
                    } else {
                        $msgs[] = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_DELETE_DATA, '<strong>' . $templates[$i]->getVar('tpl_file') . '</strong>') . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_ID, '<strong>' . $templates[$i]->getVar('tpl_id') . '</strong>');
                    }
                }
            }
            unset($templates);

            // delete blocks and block tempalte files
            $block_arr = XoopsBlock::getByModule($module->getVar('mid'));
            if (is_array($block_arr)) {
                $bcount = count($block_arr);
                $msgs[] = _AM_SYSTEM_MODULES_BLOCKS_DELETE;
                for ($i = 0; $i < $bcount; ++$i) {
                    if (false === $block_arr[$i]->delete()) {
                        $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_BLOCK_DELETE_ERROR, '<strong>' . $block_arr[$i]->getVar('name') . '</strong>') . sprintf(_AM_SYSTEM_MODULES_BLOCK_ID, '<strong>' . $block_arr[$i]->getVar('bid') . '</strong>') . '</span>';
                    } else {
                        $msgs[] = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_BLOCK_DELETE, '<strong>' . $block_arr[$i]->getVar('name') . '</strong>') . sprintf(_AM_SYSTEM_MODULES_BLOCK_ID, '<strong>' . $block_arr[$i]->getVar('bid') . '</strong>');
                    }
                    if ('' != $block_arr[$i]->getVar('template')) {
                        $templates = $tplfileHandler->find(null, 'block', $block_arr[$i]->getVar('bid'));
                        $btcount   = count($templates);
                        if ($btcount > 0) {
                            for ($j = 0; $j < $btcount; ++$j) {
                                if (!$tplfileHandler->delete($templates[$j])) {
                                    $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_BLOCK_DELETE_TEMPLATE_ERROR, $templates[$j]->getVar('tpl_file')) . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_ID, '<strong>' . $templates[$j]->getVar('tpl_id') . '</strong>') . '</span>';
                                } else {
                                    $msgs[] = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_BLOCK_DELETE_DATA, '<strong>' . $templates[$j]->getVar('tpl_file') . '</strong>') . sprintf(_AM_SYSTEM_MODULES_TEMPLATE_ID, '<strong>' . $templates[$j]->getVar('tpl_id') . '</strong>');
                                }
                            }
                        }
                        unset($templates);
                    }
                }
            }

            // delete tables used by this module
            $modtables = $module->getInfo('tables');
            if (false !== $modtables && is_array($modtables)) {
                $msgs[] = _AM_SYSTEM_MODULES_DELETE_MOD_TABLES;
                foreach ($modtables as $table) {
                    // prevent deletion of reserved core tables!
                    if (!in_array($table, $reservedTables)) {
                        $sql = 'DROP TABLE ' . $db->prefix($table);
                        if (!$db->query($sql)) {
                            $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_TABLE_DROPPED_ERROR, '<strong>' . $db->prefix($table) . '<strong>') . '</span>';
                        } else {
                            $msgs[] = '&nbsp;&nbsp;' . sprintf(_AM_SYSTEM_MODULES_TABLE_DROPPED, '<strong>' . $db->prefix($table) . '</strong>');
                        }
                    } else {
                        $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . sprintf(_AM_SYSTEM_MODULES_TABLE_DROPPED_FAILDED, '<strong>' . $db->prefix($table) . '</strong>') . '</span>';
                    }
                }
            }

            // delete comments if any
            if (0 != $module->getVar('hascomments')) {
                $msgs[]         = _AM_SYSTEM_MODULES_COMMENTS_DELETE;
                $commentHandler = xoops_getHandler('comment');
                if (false === $commentHandler->deleteByModule($module->getVar('mid'))) {
                    $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . _AM_SYSTEM_MODULES_COMMENTS_DELETE_ERROR . '</span>';
                } else {
                    $msgs[] = '&nbsp;&nbsp;' . _AM_SYSTEM_MODULES_COMMENTS_DELETED;
                }
            }

            // RMV-NOTIFY
            // delete notifications if any
            if (0 != $module->getVar('hasnotification')) {
                $msgs[] = _AM_SYSTEM_MODULES_NOTIFICATIONS_DELETE;
                if (false === xoops_notification_deletebymodule($module->getVar('mid'))) {
                    $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . _AM_SYSTEM_MODULES_NOTIFICATIONS_DELETE_ERROR . '</span>';
                } else {
                    $msgs[] = '&nbsp;&nbsp;' . _AM_SYSTEM_MODULES_NOTIFICATIONS_DELETED;
                }
            }

            // delete permissions if any
            $gpermHandler = xoops_getHandler('groupperm');
            if (false === $gpermHandler->deleteByModule($module->getVar('mid'))) {
                $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . _AM_SYSTEM_MODULES_GROUP_PERMS_DELETE_ERROR . '</span>';
            } else {
                $msgs[] = '&nbsp;&nbsp;' . _AM_SYSTEM_MODULES_GROUP_PERMS_DELETED;
            }

            // delete module config options if any
            if (0 != $module->getVar('hasconfig') || 0 != $module->getVar('hascomments')) {
                $configHandler = xoops_getHandler('config');
                $configs       = $configHandler->getConfigs(new \Criteria('conf_modid', $module->getVar('mid')));
                $confcount     = count($configs);
                if ($confcount > 0) {
                    $msgs[] = _AM_SYSTEM_MODULES_MODULE_DATA_DELETE;
                    for ($i = 0; $i < $confcount; ++$i) {
                        if (false === $configHandler->deleteConfig($configs[$i])) {
                            $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">' . _AM_SYSTEM_MODULES_CONFIG_DATA_DELETE_ERROR . sprintf(_AM_SYSTEM_MODULES_GONFIG_ID, '<strong>' . $configs[$i]->getvar('conf_id') . '</strong>') . '</span>';
                        } else {
                            $msgs[] = '&nbsp;&nbsp;' . _AM_SYSTEM_MODULES_GONFIG_DATA_DELETE . sprintf(_AM_SYSTEM_MODULES_GONFIG_ID, '<strong>' . $configs[$i]->getvar('conf_id') . '</strong>');
                        }
                    }
                }
            }

            // execute module specific install script if any
            $func = 'xoops_module_uninstall_' . $dirname;
            if (function_exists($func)) {
                if (!$func($module)) {
                    $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILED_EXECUTE, $func) . '</p>';
                } else {
                    $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILED_SUCESS, "<strong>{$func}</strong>") . '</p>';
                }
            }
            $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_OKUNINS, '<strong>' . $module->getVar('name') . '</strong>') . '</p>';
        }
        $msgs[] = '</div></div>';
        $msgs[] = '<div class="center"><a href="admin.php?fct=modulesadmin">' . _AM_SYSTEM_MODULES_BTOMADMIN . '</a></div>';
        $ret    = implode('<br>', $msgs);

        return $ret;
    }
}

/**
 * @param $mid
 *
 * @return string
 */
function xoops_module_activate($mid)
{
    // Get module handler
    /** @var XoopsModuleHandler $moduleHandler */
    $moduleHandler = xoops_getHandler('module');
    $module        = $moduleHandler->get($mid);
    require_once XOOPS_ROOT_PATH . '/class/template.php';
    xoops_template_clear_module_cache($module->getVar('mid'));
    // Display header
    $msgs[] = '<div id="xo-module-log">';
    $msgs   += xoops_module_log_header($module, _AM_SYSTEM_MODULES_ACTIVATE);
    // Change value
    $module->setVar('isactive', 1);
    if (!$moduleHandler->insert($module)) {
        $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILACT, '<strong>' . $module->getVar('name', 's') . '</strong>') . '&nbsp;' . _AM_SYSTEM_MODULES_ERRORSC . '<br>' . $module->getHtmlErrors() . '</p>';
    } else {
        $blocks = XoopsBlock::getByModule($module->getVar('mid'));
        $bcount = count($blocks);
        for ($i = 0; $i < $bcount; ++$i) {
            $blocks[$i]->setVar('isactive', 1);
            $blocks[$i]->store();
        }
        $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_OKACT, '<strong>' . $module->getVar('name', 's') . '</strong>') . '</p></div>';
    }
    //$msgs[] = '</div>';
    $msgs[] = '<div class="center"><a href="admin.php?fct=modulesadmin">' . _AM_SYSTEM_MODULES_BTOMADMIN . '</a></div>';
    $ret    = implode('<br>', $msgs);

    return $ret;
}

/**
 * @param $mid
 *
 * @return string
 */
function xoops_module_deactivate($mid)
{
    global $xoopsConfig;
    // Get module handler
    /** @var XoopsModuleHandler $moduleHandler */
    $moduleHandler = xoops_getHandler('module');
    $module        = $moduleHandler->get($mid);
    require_once XOOPS_ROOT_PATH . '/class/template.php';
    xoops_template_clear_module_cache($mid);
    // Display header
    $msgs[] = '<div id="xo-module-log">';
    $msgs   += xoops_module_log_header($module, _AM_SYSTEM_MODULES_DEACTIVATE);
    // Change value
    $module->setVar('isactive', 0);
    if ('system' === $module->getVar('dirname')) {
        $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILDEACT, '<strong>' . $module->getVar('name') . '</strong>') . '&nbsp;' . _AM_SYSTEM_MODULES_ERRORSC . '<br> - ' . _AM_SYSTEM_MODULES_SYSNO . '</p>';
    } elseif ($module->getVar('dirname') == $xoopsConfig['startpage']) {
        $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILDEACT, '<strong>' . $module->getVar('name') . '</strong>') . '&nbsp;' . _AM_SYSTEM_MODULES_ERRORSC . '<br> - ' . _AM_SYSTEM_MODULES_STRTNO . '</p>';
    } else {
        if (!$moduleHandler->insert($module)) {
            $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILDEACT, '<strong>' . $module->getVar('name') . '</strong>') . '&nbsp;' . _AM_SYSTEM_MODULES_ERRORSC . '<br>' . $module->getHtmlErrors() . '</p>';
        } else {
            $blocks = XoopsBlock::getByModule($module->getVar('mid'));
            $bcount = count($blocks);
            for ($i = 0; $i < $bcount; ++$i) {
                $blocks[$i]->setVar('isactive', 0);
                $blocks[$i]->store();
            }
            $msgs[] = '<p>' . sprintf(_AM_SYSTEM_MODULES_OKDEACT, '<strong>' . $module->getVar('name') . '</strong>') . '</p>';
        }
    }
    $msgs[] = '<div class="center"><a href="admin.php?fct=modulesadmin">' . _AM_SYSTEM_MODULES_BTOMADMIN . '</a></div>';
    $ret    = implode('<br>', $msgs);

    return $ret;
}

/**
 * @param $mid
 * @param $name
 *
 * @return string
 */
function xoops_module_change($mid, $name)
{
    /** @var XoopsModuleHandler $moduleHandler */
    $moduleHandler = xoops_getHandler('module');
    $module        = $moduleHandler->get($mid);
    $module->setVar('name', $name);
    $myts = \MyTextSanitizer::getInstance();
    if (!$moduleHandler->insert($module)) {
        $ret = '<p>' . sprintf(_AM_SYSTEM_MODULES_FAILORDER, '<strong>' . $myts->stripSlashesGPC($name) . '</strong>') . '&nbsp;' . _AM_SYSTEM_MODULES_ERRORSC . '<br>';
        $ret .= $module->getHtmlErrors() . '</p>';

        return $ret;
    }

    return '<p>' . sprintf(_AM_SYSTEM_MODULES_OKORDER, '<strong>' . $myts->stripSlashesGPC($name) . '</strong>') . '</p>';
}

/**
 * @param $module
 * @param $title
 *
 * @return array
 */
function xoops_module_log_header($module, $title)
{
    $msgs[] = '<div class="header">';
    $msgs[] = $errs[] = '<h4>' . $title . $module->getInfo('name', 's') . '</h4>';
    if (false !== $module->getInfo('image') && '' != trim($module->getInfo('image'))) {
        $msgs[] = '<img src="' . XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/' . trim($module->getInfo('image')) . '" alt="">';
    }
    $msgs[] = '<strong>' . _VERSION . ':</strong> ' . $module->getInfo('version') . '&nbsp;' . $module->getInfo('module_status');
    if (false !== $module->getInfo('author') && '' != trim($module->getInfo('author'))) {
        $msgs[] = '<strong>' . _AUTHOR . ':</strong> ' . htmlspecialchars(trim($module->getInfo('author')));
    }
    $msgs[] = '</div>';

    return $msgs;
}
