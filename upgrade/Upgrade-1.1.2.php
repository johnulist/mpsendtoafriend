<?php
/**
 * 2016 Mijn Presta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mijnpresta.nl so we can send you a copy immediately.
 *
 *  @author    Michael Dekker <info@mijnpresta.nl>
 *  @copyright 2016 Mijn Presta
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_2($module)
{
    foreach ($module->controllers as $controller) {
        $page = 'module-'.$module->name.'-'.$controller;

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('meta');
        $sql->where('`page` = \''.pSQL($page).'\'');
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if ((int) $result > 0) {
            continue;
        }

        $meta = new Meta();
        $meta->page = $page;
        $meta->configurable = 1;
        $meta->save();
        if ((int) $meta->id <= 0) {
            Context::getContext()->controller->errors[] = sprintf(Tools::displayError('Unable to install controller: %s'), $controller);

            return false;
        }
    }

    @unlink(_PS_MODULE_DIR_.$module->name.'/'.$module->name.'_ajax.php');

    return true;
}
