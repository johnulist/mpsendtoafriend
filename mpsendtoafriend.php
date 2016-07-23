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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class MpSendToAFriend
 */
class MpSendToAFriend extends Module
{
    public $context;
    public $secureKey;
    public $pageName;

    /**
     * MpSendToAFriend constructor.
     * @param bool $dontTranslate
     */
    public function __construct($dontTranslate = false)
    {
        $this->name = 'mpsendtoafriend';
        $this->version = '1.9.0';
        $this->author = 'Mijn Presta';
        $this->tab = 'front_office_features';
        $this->need_instance = 0;
        $this->secureKey = Tools::encrypt($this->name);

        parent::__construct();

        if (!$dontTranslate) {
            $this->displayName = $this->l('Send to a Friend module');
            $this->description = $this->l('Allows customers to send a product link to a friend.');
        }
    }

    /**
     * Install the module
     *
     * @return bool Whether the module has been successfully installed
     * @throws PrestaShopException
     */
    public function install()
    {
        return (parent::install() && $this->registerHook('extraLeft') && $this->registerHook('header'));
    }

    /**
     * Uninstall the module
     *
     * @return bool Whether the module has been successfully uninstalled
     */
    public function uninstall()
    {
        return (parent::uninstall() && $this->unregisterHook('header') && $this->unregisterHook('extraLeft'));
    }

    /**
     * Hook to Extra Left
     *
     * @param array $params Hook parameters
     * @return string Hook HTML
     */
    public function hookExtraLeft($params)
    {
        $product = new Product((int) Tools::getValue('id_product'), false, $this->context->language->id);
        $image = Product::getCover((int) $product->id);

        $this->context->smarty->assign(
            array(
                'stf_product' => $product,
                'stf_product_cover' => (int) $product->id.'-'.(int) $image['id_image'],
                'stf_secure_key' => $this->secureKey,
            )
        );

        if (Module::isEnabled('mprecaptcha') && Configuration::get(Mprecaptcha::SENDTOAFRIEND)) {
            $this->context->smarty->assign('sitekey', Configuration::get(Mprecaptcha::PUBLIC_KEY));
        }

        if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            return $this->context->smarty->fetch($this->local_path.'views/templates/front/sendtoafriend-extra15.tpl');
        }

        return $this->context->smarty->fetch($this->local_path.'views/templates/front/sendtoafriend-extra16.tpl');
    }

    /**
     * Hook to Front Office Header
     *
     * @param array $params Hook parameters
     */
    public function hookHeader($params)
    {
        $this->pageName = Dispatcher::getInstance()->getController();
        if ($this->pageName == 'product') {
            if (Module::isEnabled('mprecaptcha')) {
                $mprecaptcha = Module::getInstanceByName('mprecaptcha');
                if (Configuration::get($mprecaptcha::SENDTOAFRIEND)) {
                    $this->context->controller->addJS('https://www.google.com/recaptcha/api.js');
                }
            }
            if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
                $this->context->controller->addCSS($this->local_path.'views/css/mpsendtoafriend15.css', 'all');
                $this->context->controller->addJS($this->local_path.'views/js/mpsendtoafriend15.js');
            } else {
                $this->context->controller->addCSS($this->local_path.'views/css/mpsendtoafriend16.css', 'all');
                $this->context->controller->addJS($this->local_path.'views/js/mpsendtoafriend16.js');
            }
        }
    }

    /**
     * Is valid name validation
     *
     * @param string $name Name
     * @return bool Whether the name is valid
     */
    public function isValidName($name)
    {
        $isName = Validate::isName($name);
        $isShortName = $this->isShortName($name);
        $isNameLikeAnUrl = $this->isNameLikeAnUrl($name);
        $isValidName = $isName && $isShortName && !$isNameLikeAnUrl;

        return $isValidName;
    }

    /**
     * Validate if is short name
     *
     * @param string $name Name
     * @return bool Whether the string is a short name
     */
    public function isShortName($name)
    {
        $isShortName = (strlen($name) <= 50);

        return $isShortName;
    }

    /**
     * Validate if name is like a URL
     *
     * @param string $name Name
     * @return bool Whether the name is like a URL
     */
    public function isNameLikeAnUrl($name)
    {
        // THIS REGEX IS NOT MEANT TO FIND A VALID URL
        // the goal is to see if the given string for a Person Name is containing something similar to an url
        //
        // See all strings that i tested the regex against in https://regex101.com/r/yL7lU0/3
        //
        // Please fork the regex if you can improve it and make a Pull Request
        $regex = "/(https?:[\/]*.*)|([\.]*[[[:alnum:]]+\.[^ ]]*.*)/m";
        $isNameLikeAnUrl = (bool) preg_match_all($regex, $name);

        return $isNameLikeAnUrl;
    }
}
