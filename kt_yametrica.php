<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Kt_YaMetrica extends Module
{
    const PREFIX = 'kt_yametrica';
    
    const SETTING = self::PREFIX.'ACCOUNT';
    
    const HOOK = 'displayHeader';
    
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'kt_yametrica';
        $this->tab = 'seo';
        $this->version = '0.1.0';
        $this->author = 'Konstantin Toporov';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Add yandex metrica');
        $this->description = $this->l('Add yandex metrica to your store');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue(self::SETTING, '');

        return parent::install() &&
                $this->registerHook(self::HOOK);
    }

    public function uninstall()
    {
        Configuration::deleteByName(self::SETTING);

        return parent::uninstall() &&
                $this->unregisterHook(self::HOOK);
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitKt_yametricaModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        
        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitKt_yametricaModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('ID of yandex metrica'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        //'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a yandex metrica ID'),
                        'name' => self::SETTING,
                        'label' => $this->l('ID of a yandex metrica account'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
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
            self::SETTING => Configuration::get(self::SETTING),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        Configuration::updateValue(self::SETTING, Tools::getValue(self::SETTING));
    }
    
    public function hookDisplayHeader()
    {
        $googleID = Configuration::get(self::SETTING);
        
        if (empty($googleID)) {
            return;
        }
        
        $this->context->smarty->assign(array(
                'kt_yamenticaID' => Configuration::get(self::SETTING),
            ));
        
        return $this->context->smarty->fetch($this->local_path.'views/templates/hooks/metrica.tpl');
    }
}
