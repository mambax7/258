<?php

/**
 *
 * This plugin provides an HTTP based image upload handler for the <i>upload/images</i> admin tab.
 *
 * @package    plugins
 * @subpackage uploader
 *
 */
$plugin_is_filter   = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('<em>http</em> image upload handler.');
$plugin_author      = 'Stephen Billard (sbillard)';

if (OFFSET_PATH == 2) {
    setoptiondefault('zp_plugin_uploader_http', $plugin_is_filter);
}

if (zp_loggedin(UPLOAD_RIGHTS)) {
    zp_register_filter('uploadHandlers', 'httpUploadHandler');
    zp_register_filter('admin_tabs', 'httpUploadHandler_admin_tabs', 10);
}

/**
 * @param $uploadHandlers
 * @return mixed
 */
function httpUploadHandler($uploadHandlers)
{
    $uploadHandlers['http'] = SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/uploader_http';

    return $uploadHandlers;
}

/**
 * @param $tabs
 * @return mixed
 */
function httpUploadHandler_admin_tabs($tabs)
{
    $me     = sprintf(gettext('images (%s)'), 'http');
    $mylink = 'admin-upload.php?page=upload&tab=http&type=' . gettext('images');
    if (null === $tabs['upload']) {
        $tabs['upload'] = array(
            'text'    => gettext('upload'),
            'link'    => WEBPATH . '/' . ZENFOLDER . '/admin-upload.php',
            'subtabs' => null
        );
    }
    $tabs['upload']['subtabs'][$me] = $mylink;
    if (zp_getcookie('uploadtype') === 'http') {
        $tabs['upload']['link'] = $mylink;
    }

    return $tabs;
}