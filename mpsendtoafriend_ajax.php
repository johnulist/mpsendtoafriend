<?php
/**
 * Copyright (C) Mijn Presta - All Rights Reserved
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 *
 * @author    Michael Dekker <prestashopaddons@mijnpresta.nl>
 * @copyright 2015 Mijn Presta
 * @license   proprietary
 * Intellectual Property of Mijn Presta
 */

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__).'/mpsendtoafriend.php');

/** @var MpSendToAFriend $module */
$module = new MpSendToAFriend();

if (Module::isEnabled('mpsendtoafriend') && Tools::getValue('action') == 'sendToMyFriend' && Tools::getValue('secure_key') == $module->secureKey) {
    if (Module::isEnabled('mprecaptcha')) {
        $recaptcha = Module::getInstanceByName('mprecaptcha');
        if (Configuration::get($recaptcha::SENDTOAFRIEND)) {
            require_once _PS_MODULE_DIR_.'mprecaptcha/lib/recaptchalib.php';
            $recaptchalib = new MpReCaptchaLib(Configuration::get('MPRECAPTCHA_PRIVATE_KEY'));
            $resp = $recaptchalib->verifyResponse(Tools::getRemoteAddr(), Tools::getValue('recaptcha'));

            if ($resp == null || !($resp->success)) {
                die('0');
            }
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
    )
    ) {
        die('0');
    }
    die('1');
}
die('0');
