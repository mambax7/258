<?php
/**
 * @return array
 */
function b_waiting_mylinks()
{
    $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();
    $ret     = [];

    // mylinks links
    $block  = [];
    $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('mylinks_links') . ' WHERE status=0');
    if ($result) {
        $block['adminlink'] = XOOPS_URL . '/modules/mylinks/admin/main.php?op=listNewLinks';
        list($block['pendingnum']) = $xoopsDB->fetchRow($result);
        $block['lang_linkname'] = _PI_WAITING_WAITINGS;
    }
    $ret[] = $block;

    // mylinks broken
    $block      = [];
    $bknHandler = xoops_getModuleHandler('broken', 'mylinks');
    $result     = $bknHandler->getCount();
    //    $result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mylinks_broken"));
    if ($result) {
        $block['adminlink'] = $xoops->url('modules/mylinks/admin/main.php?op=listBrokenLinks');
        //        list($block['pendingnum']) = $xoopsDB->fetchRow($result);
        $block['pendingnum']    = $result;
        $block['lang_linkname'] = _PI_WAITING_BROKENS;
    }
    $ret[] = $block;

    // mylinks modreq
    $block         = [];
    $modreqHandler = xoops_getModuleHandler('modification', 'mylinks');
    $result        = $modreqHandler->getCount();
    //    $result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mylinks_mod"));
    if ($result) {
        $block['adminlink'] = XOOPS_URL . '/modules/mylinks/admin/main.php?op=listModReq';
        //        list($block['pendingnum']) = $xoopsDB->fetchRow($result);
        $block['pendingnum']    = $result;
        $block['lang_linkname'] = _PI_WAITING_MODREQS;
    }
    $ret[] = $block;

    return $ret;
}
