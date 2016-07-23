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

/**
 * Class MpSendToAFriend
 */
class MpSendToAFriend extends Module
{
    public $context;
    public $secureKey;
    public $pageName;

    const CAPTCHA = 'MPSENDTOAFRIEND_CAPTCHA';
    const PUBLIC_KEY = 'MPSENDTOAFRIEND_PUBLIC_KEY';
    const PRIVATE_KEY = 'MPSENDTOAFRIEND_PRIVATE_KEY';

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

        $this->bootstrap = true;

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
     * Display module configuration page
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit'.$this->name)) {
            $this->postProcess();
        }

        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        $output .= $this->displayForm();

        return $output;
    }

    /**
     * @return string
     */
    public function displayForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        $input = array(
            array(
                'type' => 'text',
                'label' => $this->l('reCAPTCHA site key'),
                'name' => self::PUBLIC_KEY,
                'size' => 64,
                'desc' => $this->l('Used in the Javascript files that are served to users.'),
                'required' => true,
            ),
            array(
                'type' => 'text',
                'label' => $this->l('reCAPTCHA secret key'),
                'name' => self::PRIVATE_KEY,
                'desc' => $this->l('Used for communication between the store and Google. Be sure to keep this key a secret.'),
                'size' => 64,
                'required' => true,
            ),
            array(
                'type' => 'hr',
                'name' => '',
            ),
            array(
                'type' => 'switch',
                'label' => $this->l('Enable captcha'),
                'name' => self::CAPTCHA,
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                    ),
                ),
            ),
        );

        return array(
            'form' => array(
                'legend' => array(
                    'title' => Translate::getAdminTranslation('Settings', 'AdminReferrers'),
                    'icon' => 'icon-cogs',
                ),
                'input' => $input,
                'submit' => array(
                    'title' => Translate::getAdminTranslation('Save', 'AdminReferrers'),
                    'class' => (version_compare(_PS_VERSION_, '1.6', '<') ? 'button' : null),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            self::PRIVATE_KEY => Configuration::get(self::PRIVATE_KEY),
            self::PUBLIC_KEY => Configuration::get(self::PUBLIC_KEY),
            self::CAPTCHA => Configuration::get(self::CAPTCHA),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        Configuration::updateValue(self::PRIVATE_KEY, Tools::getValue(self::PRIVATE_KEY));
        Configuration::updateValue(self::PUBLIC_KEY, Tools::getValue(self::PUBLIC_KEY));
        Configuration::updateValue(self::CAPTCHA, Tools::getValue(self::CAPTCHA));

        $this->context->controller->confirmations[] = $this->l('Successfully updated.');
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

        if (Configuration::get(self::PRIVATE_KEY)
            && Configuration::get(self::PUBLIC_KEY)
            && Configuration::get(self::CAPTCHA)) {
            $this->context->smarty->assign('sitekey', Configuration::get(self::PUBLIC_KEY));
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
            if (Configuration::get(self::PRIVATE_KEY)
                && Configuration::get(self::PUBLIC_KEY)
                &&Configuration::get(self::CAPTCHA)) {
                $this->context->controller->addJS('https://www.google.com/recaptcha/api.js');
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
