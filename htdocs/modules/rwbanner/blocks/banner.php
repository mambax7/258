<?php
/*
                                  RW-Banner
                          Copyright (c) 2006 BrInfo
                          <http://www.brinfo.com.br>
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  You may not change or alter any portion of this comment or credits
  of supporting developers from this source code or any supporting
  source code which is considered copyrighted (c) material of the
  original comment or credit authors.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
*/
/**
 * XOOPS rwbanner Banner Block
 *
 * @copyright::  {@link www.brinfo.com.br BrInfo - Soluções Web}
 * @license  ::    {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @author   ::     Rodrigo Pereira Lima aka RpLima (http://www.brinfo.com.br)
 * @package  ::    rwbanner
 * @since    ::      1.0
 * @param $options
 * @return array
 */

use XoopsModules\Rwbanner;

function exibe_banner($options)
{
    // require_once __DIR__ . '/../class/class.categoria.php';
    // require_once __DIR__ . '/../class/class.banner.php';
    //    require_once (dirname(__DIR__) .'/admin/admin_header.php');

    global $xoopsTpl;
    $dirname = basename(dirname(__DIR__));
    $xoopsTpl->assign('module_dir', $dirname);

    $block = [];

    //recebendo parâmetros de configuração
    $block['categ'] = $options[0];
    $block['qtde']  = $options[1];
    $block['cols']  = $options[2];
    $block['redim'] = $options[3];
    $block['title'] = _MI_RWBANNER_BLOCK1_NAME;

    $categ         = new Rwbanner\Categoria(null, $options[0]);
    $block['larg'] = $categ->getLarg();
    $block['alt']  = $categ->getAlt();

    $banner = new Rwbanner\Banner();
    $arr    = $banner->getBanners(false, 'ORDER BY RAND()', $options[0], $options[1]);

    $arr2 = [];
    $arr3 = [];
    for ($i = 0; $i <= count($arr) - 1; ++$i) {
        $arr[$i]->inchits();
        foreach ($arr[$i] as $key => $value) {
            $arr2[$key] = $value;
        }
        $arr3[] = $arr2;
    }
    for ($i = 0; $i <= count($arr3) - 1; ++$i) {
        if (false !== stripos($arr3[$i]['grafico'], '.swf')) {
            $arr3[$i]['swf'] = 1;
            $arq             = explode('/', $arr3[$i]['grafico']);
            $grafico1        = _RWBANNER_DIRIMAGES . '/' . $arq[count($arq) - 1];
            // require_once __DIR__ . '/../class/FlashHeader.php';
            $f               = new Rwbanner\FlashHeader($grafico1);
            $result          = $f->getimagesize();
            $arr3[$i]['fps'] = $result['frameRate'];
        }
    }
    $block['banners'] = $arr3;

    return $block;
}

/**
 * @param $options
 * @return string
 */
function edita_banner($options)
{
    global $xoopsDB;
    $query    = 'SELECT cod,titulo FROM ' . $xoopsDB->prefix('rwbanner_categorias');
    $consulta = $xoopsDB->queryF($query);
    $categ    = _MB_RWBANNER_OPTION1 . "&nbsp;<select options[0] name=\"options[0]\" onchange='javascript:options0.value = this.value;'>";
    while (false !== (list($cod, $titulo) = $xoopsDB->fetchRow($consulta))) {
        if ($options[0] == $cod) {
            $sel = 'selected';
        } else {
            $sel = '';
        }
        $categ .= '<option value="' . $cod . '" ' . $sel . '>' . $titulo . '</option>';
    }
    $categ .= '</select>';
    $form  = $categ;
    //Quantidade de banners à exibir no bloco
    $qtde = _MB_RWBANNER_OPTION2 . "&nbsp;<input type='text' name='options[]' value='" . $options[1] . "' onchange='javascript:options1.value = this.value;'>";
    $form .= '<br>' . $qtde;
    //Quantidade de colunas em que os banners serão exibidos
    $qtde = _MB_RWBANNER_OPTION3 . "&nbsp;<input type='text' name='options[]' value='" . $options[2] . "' onchange='javascript:options2.value = this.value;'>";
    $form .= '<br>' . $qtde;
    //Redimensionar imagens?
    if (1 == $options[3]) {
        $check = 'checked';
    } else {
        $check = '';
    }
    $qtde = _MB_RWBANNER_OPTION4 . "&nbsp;<input type='checkbox' name='options[]' value='1' " . $check . " onchange='javascript:options3.value = this.value;'>" . _YES;
    $form .= '<br>' . $qtde . '<br>' . _MB_RWBANNER_OPTION4_DESC;

    return $form;
}
