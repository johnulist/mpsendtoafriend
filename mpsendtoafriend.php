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

    /** @var string $baseUrl Module base URL */
    public $baseUrl;
    public $latestVersion;
    public $lastCheck;
    public $downloadUrl;
    public $needsUpdate;

    const CAPTCHA = 'MPSENDTOAFRIEND_CAPTCHA';
    const PUBLIC_KEY = 'MPSENDTOAFRIEND_PUBLIC_KEY';
    const PRIVATE_KEY = 'MPSENDTOAFRIEND_PRIVATE_KEY';

    const LAST_CHECK = 'MPSENDTOAFRIEND_LAST_CHECK';
    const LAST_UPDATE = 'MPSENDTOAFRIEND_LAST_UPDATE';
    const LATEST_VERSION = 'MPSENDTOAFRIEND_LATEST_VERSION';
    const DOWNLOAD_URL = 'MPSENDTOAFRIEND_DOWNLOAD_URL';
    const CHECK_INTERVAL = 86400;
    const UPDATE_INTERVAL = 60;

    const GITHUB_USER = 'firstred';
    const GITHUB_REPO = 'mpsendtoafriend';

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

        // Only check from Back Office
        if ($this->context->cookie->id_employee) {
            $this->baseUrl = $this->context->link->getAdminLink('AdminModules', true).'&'.http_build_query(array(
                    'configure' => $this->name,
                    'tab_module' => $this->tab,
                    'module_name' => $this->name,
                ));

            $this->lastCheck = Configuration::get(self::LAST_CHECK);
            $this->checkUpdate();
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
        return parent::install()
            && $this->registerHook('extraLeft')
            && $this->registerHook('header')
            && Configuration::updateGlobalValue(self::LATEST_VERSION, '0.0.0');
    }

    /**
     * Uninstall the module
     *
     * @return bool Whether the module has been successfully uninstalled
     */
    public function uninstall()
    {
        return parent::uninstall()
            && $this->unregisterHook('header')
            && $this->unregisterHook('extraLeft')
            && Configuration::deleteByName(self::LATEST_VERSION)
            && Configuration::deleteByName(self::DOWNLOAD_URL);
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

        $this->baseUrl = $this->context->link->getAdminLink('AdminModules', true).'&'.http_build_query(array(
            'configure' => $this->name,
            'tab_module' => $this->tab,
            'module_name' => $this->name,
        ));

        $this->context->smarty->assign(array(
            'module_url' => $this->baseUrl,
            'curentVersion' => $this->version,
            'latestVersion' => $this->latestVersion,
            'lastCheck' => $this->lastCheck,
            'needsUpdate' => $this->needsUpdate,
            'baseUrl' => $this->baseUrl,
        ));

        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/versioncheck.tpl');
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

        if (Tools::getValue('mpsendtoafriendCheckUpdate') || Tools::getValue('mpsendtoafriendApplyUpdate')) {
            $this->checkUpdate();
        }

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

    /**
     * Check for module updates
     */
    protected function checkUpdate()
    {
        $lastCheck = (int) Configuration::get(self::LAST_CHECK);
        $lastUpdate = (int) Configuration::get(self::LAST_UPDATE);

        if ($lastCheck < (time() - self::CHECK_INTERVAL) || Tools::getValue($this->name.'CheckUpdate')) {
            $this->lastCheck = time();
            Configuration::updateGlobalValue(self::LAST_CHECK, time());

            // Initialize GitHub Client
            $client = new \Github\Client(
                new \Github\HttpClient\CachedHttpClient(array('cache_dir' => '/tmp/github-api-cache'))
            );


            // Check the release tag
            try {
                $latestRelease = $client->api('repo')->releases()->latest(self::GITHUB_USER, self::GITHUB_REPO);
                if (isset($latestRelease['tag_name']) && $latestRelease['tag_name']) {
                    if (version_compare($this->version, $latestRelease['tag_name'], '<') &&
                        isset($latestRelease['assets'][0]['browser_download_url'])) {
                        Configuration::updateGlobalValue(self::LATEST_VERSION, $latestRelease['tag_name']);
                        Configuration::updateGlobalValue(self::DOWNLOAD_URL, $latestRelease['assets'][0]['browser_download_url']);
                        $this->latestVersion = $latestRelease['tag_name'];
                        $this->downloadUrl = $latestRelease['assets'][0]['browser_download_url'];
                    }
                }
            } catch (Exception $e) {
                $this->addWarning($e->getMessage());
            }
        } else {
            $this->latestVersion = Configuration::get(self::LATEST_VERSION);
            $this->downloadUrl = Configuration::get(self::DOWNLOAD_URL);
        }

        $this->needsUpdate = version_compare($this->version, $this->latestVersion, '<');

        if ($this->needsUpdate &&
            ($lastUpdate < (time() - self::UPDATE_INTERVAL) || Tools::getValue($this->name.'ApplyUpdate'))
        ) {
            $zipLocation = _PS_MODULE_DIR_.$this->name.'.zip';
            if (@!file_exists($zipLocation)) {
                file_put_contents($zipLocation, fopen($this->downloadUrl, 'r'));
            }
            if (@file_exists($zipLocation)) {
                $this->extractArchive($zipLocation);
            } else {
                // We have an outdated URL, reset last check
                Configuration::updateGlobalValue(self::LAST_CHECK, 0);
            }
        }
    }

    /**
     * Add information message
     *
     * @param string $message Message
     */
    protected function addInformation($message)
    {
        if (!Tools::isSubmit('configure')) {
            $this->context->controller->informations[] = '<a href="'.$this->baseUrl.'">'.$this->displayName.': '.$message.'</a>';
        } else {
            $this->context->controller->informations[] = $message;
        }
    }

    /**
     * Add confirmation message
     *
     * @param string $message Message
     */
    protected function addConfirmation($message)
    {
        if (!Tools::isSubmit('configure')) {
            $this->context->controller->confirmations[] = '<a href="'.$this->baseUrl.'">'.$this->displayName.': '.$message.'</a>';
        } else {
            $this->context->controller->confirmations[] = $message;
        }
    }

    /**
     * Add warning message
     *
     * @param string $message Message
     */
    protected function addWarning($message)
    {
        if (!Tools::isSubmit('configure')) {
            $this->context->controller->warnings[] = '<a href="'.$this->baseUrl.'">'.$this->displayName.': '.$message.'</a>';
        } else {
            $this->context->controller->warnings[] = $message;
        }
    }

    /**
     * Add error message
     *
     * @param string $message Message
     */
    protected function addError($message)
    {
        if (!Tools::isSubmit('configure')) {
            $this->context->controller->errors[] = '<a href="'.$this->baseUrl.'">'.$this->displayName.': '.$message.'</a>';
        } else {
            // Do not add error in this case
            // It will break execution of AdminController
            $this->context->controller->warnings[] = $message;
        }
    }

    /**
     * Validate GitHub repository
     *
     * @param string $repo Repository: username/repository
     * @return bool Whether the repository is valid
     */
    protected function validateRepo($repo)
    {
        return count(explode('/', $repo)) === 2;
    }

    /**
     * Extract module archive
     *
     * @param string $file     File location
     * @param bool   $redirect Whether there should be a redirection after extracting
     * @return bool
     */
    protected function extractArchive($file, $redirect = true)
    {
        $zipFolders = array();
        $tmpFolder = _PS_MODULE_DIR_.'selfupdate'.md5(time());

        if (@!file_exists($file)) {
            $this->addError($this->l('Module archive could not be downloaded'));

            return false;
        }

        $success = false;
        if (substr($file, -4) == '.zip') {
            if (Tools::ZipExtract($file, $tmpFolder) && file_exists($tmpFolder.DIRECTORY_SEPARATOR.$this->name)) {
                if (@rename(_PS_MODULE_DIR_.$this->name, _PS_MODULE_DIR_.$this->name.'backup') && @rename($tmpFolder.DIRECTORY_SEPARATOR.$this->name, _PS_MODULE_DIR_.$this->name)) {
                    $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_.$this->name.'backup');
                    $success = true;
                } else {
                    if (file_exists(_PS_MODULE_DIR_.$this->name.'backup')) {
                        $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_.$this->name);
                        @rename(_PS_MODULE_DIR_.$this->name.'backup', _PS_MODULE_DIR_.$this->name);
                    }
                }
            }
        } else {
            require_once(_PS_TOOL_DIR_.'tar/Archive_Tar.php');
            $archive = new Archive_Tar($file);
            if ($archive->extract($tmpFolder)) {
                $zipFolders = scandir($tmpFolder);
                if ($archive->extract(_PS_MODULE_DIR_)) {
                    $success = true;
                }
            }
        }

        if (!$success) {
            $this->addError($this->l('There was an error while extracting the update (file may be corrupted).'));
            // Force a new check
            Configuration::updateGlobalValue(self::LAST_CHECK, 0);
        } else {
            //check if it's a real module
            foreach ($zipFolders as $folder) {
                if (!in_array($folder, array('.', '..', '.svn', '.git', '__MACOSX')) && !Module::getInstanceByName($folder)) {
                    $this->addError(sprintf($this->l('The module %1$s that you uploaded is not a valid module.'), $folder));
                    $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_.$folder);
                }
            }
        }

        @unlink($file);
        $this->recursiveDeleteOnDisk($tmpFolder);


        if ($success) {
            Configuration::updateGlobalValue(self::LAST_UPDATE, (int) time());
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            if ($redirect) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&doNotAutoUpdate=1');
            }
        }

        return $success;
    }

    /**
     * Delete folder recursively
     *
     * @param string $dir Directory
     */
    protected function recursiveDeleteOnDisk($dir)
    {
        if (strpos(realpath($dir), realpath(_PS_MODULE_DIR_)) === false) {
            return;
        }

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir.'/'.$object) == 'dir') {
                        $this->recursiveDeleteOnDisk($dir.'/'.$object);
                    } else {
                        @unlink($dir.'/'.$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
