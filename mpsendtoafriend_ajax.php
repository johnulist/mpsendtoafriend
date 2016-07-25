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

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__).'/mpsendtoafriend.php');

/** @var MpSendToAFriend $module */
$module = new MpSendToAFriend();

if (Module::isEnabled('mpsendtoafriend') && Tools::getValue('action') == 'sendToMyFriend' && Tools::getValue('secure_key') == $module->secureKey) {
    if (Configuration::get(MpSendToAFriend::PRIVATE_KEY)
        && Configuration::get(MpSendToAFriend::PUBLIC_KEY)
        && Configuration::get(MpSendToAFriend::CAPTCHA)) {
        if (!class_exists('RecaptchaLib')) {
            require_once _PS_MODULE_DIR_.'mpsendtoafriend/lib/recaptchalib.php';
        }
        $recaptchalib = new ReCaptchaLib(Configuration::get(MpSendToAFriend::PRIVATE_KEY));
        $resp = $recaptchalib->verifyResponse(Tools::getRemoteAddr(), Tools::getValue('g-recaptcha-response'));

        if ($resp == null || !($resp->success)) {
            die('0');
        }
    }

    // Retrocompatibilty with old theme
    if ($friend = Tools::getValue('friend')) {
        $friend = Tools::jsonDecode($friend, true);

        foreach ($friend as $key => $value) {
            if ($value['key'] == 'friend_name') {
                $friendName = $value['value'];
            } elseif ($value['key'] == 'friend_email') {
                $friendMail = $value['value'];
            } elseif ($value['key'] == 'id_product') {
                $idProduct = $value['value'];
            }
        }
    } else {
        $friendName = Tools::getValue('name');
        $friendMail = Tools::getValue('email');
        $idProduct = Tools::getValue('id_product');
    }

    if (!$friendName || !$friendMail || !$idProduct) {
        die('0');
    }

    $isValidEmail = Validate::isEmail($friendMail);
    $isValidName = $module->isValidName($friendName);

    if (false === $isValidName || false === $isValidEmail) {
        die('0');
    }

    /* Email generation */
    $product = new Product((int) $idProduct, false, $module->context->language->id);
    $productLink = $module->context->link->getProductLink($product);
    $customer = $module->context->cookie->customer_firstname ? $module->context->cookie->customer_firstname.' '.$module->context->cookie->customer_lastname : $module->l('A friend', 'sendtoafriend_ajax');

    $templateVars = array(
        '{product}' => $product->name,
        '{product_link}' => $productLink,
        '{customer}' => $customer,
        '{name}' => Tools::safeOutput($friendName),
    );

    /* Email sending */
    if (!Mail::Send(
        (int) $module->context->cookie->id_lang,
        'send_to_a_friend',
        sprintf(Mail::l('%1$s sent you a link to %2$s', (int) $module->context->cookie->id_lang), $customer, $product->name),
        $templateVars,
        $friendMail,
        null,
        ($module->context->cookie->email ? $module->context->cookie->email : null),
        ($module->context->cookie->customer_firstname ? $module->context->cookie->customer_firstname.' '.$module->context->cookie->customer_lastname : null),
        null,
        null,
        dirname(__FILE__).'/mails/'
    )) {
        die('0');
    }
    die('1');
}
die('0');
