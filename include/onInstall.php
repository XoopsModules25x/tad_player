<?php

include dirname(__DIR__) . '/preloads/autoloader.php';

function xoops_module_install_tad_player(&$module)
{
    tad_player_mk_dir(XOOPS_ROOT_PATH . '/uploads/tad_player');
    tad_player_mk_dir(XOOPS_ROOT_PATH . '/uploads/tad_player/file');
    tad_player_mk_dir(XOOPS_ROOT_PATH . '/uploads/tad_player/image');
    tad_player_mk_dir(XOOPS_ROOT_PATH . '/uploads/tad_player/image/.thumbs');
    tad_player_mk_dir(XOOPS_ROOT_PATH . '/uploads/tad_player/img');
    tad_player_mk_dir(XOOPS_ROOT_PATH . '/uploads/tad_player/flv');
    tad_player_mk_dir(XOOPS_ROOT_PATH . '/uploads/tad_player_batch_uploads');

    return true;
}

//建立目錄
function tad_player_mk_dir($dir = '')
{
    //若無目錄名稱秀出警告訊息
    if (empty($dir)) {
        return;
    }
    //若目錄不存在的話建立目錄
    if (!is_dir($dir)) {
        umask(000);
        //若建立失敗秀出警告訊息
        mkdir($dir, 0777);
    }
}
