<?php
/**
 * 2022-2023 PrestaShop.
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
 * @author    shoparize <partner@shoparize.com>
 * @copyright 2022-2023 shoparize
 * @license   http://www.gnu.org/licenses/gpl-3.0.html (GPLv3 or later License)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/include.php';

class Shoparizepartner extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'shoparizepartner';
        $this->tab = 'analytics_stats';
        $this->version = '1.2.0';
        $this->author = 'Shoparize';
        $this->need_instance = 1;

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Shoparize Partner');
        $this->description = $this->l(
            'Shoparize’s superior customer service, with a merchant portal to feed and campaign optimization tools, easily adapts to merchant’s needs to drive incremental revenue, even while operating alongside another CSS.
        '
        );

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall my module?');

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->module_key = 'f585f0c7c635e55e5702fdfda49ac693';
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update.
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        Configuration::updateValue('SHOPARIZEPARTNER_SHOP_ID', '');
        Configuration::updateValue('SHOPARIZEPARTNER_COLOR_ATTR_GROUP', 0);
        Configuration::updateValue('SHOPARIZEPARTNER_SIZE_ATTR_GROUP', 0);

        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('moduleRoutes');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SHOPARIZEPARTNER_SHOP_ID');
        Configuration::deleteByName('SHOPARIZEPARTNER_COLOR_ATTR_GROUP');
        Configuration::deleteByName('SHOPARIZEPARTNER_SIZE_ATTR_GROUP');

        return parent::uninstall();
    }

    /**
     * Load the configuration form.
     */
    public function getContent()
    {
        /*
         * If values have been submitted in the form, process.
         */
        if (Tools::isSubmit('submitShoparizepartnerModule')) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
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
        $helper->submit_action = 'submitShoparizepartnerModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $attributeGroups = AttributeGroup::getAttributesGroups(Context::getContext()->language->id);
        array_unshift($attributeGroups, [
            'name' => 'no attribute',
            'id_attribute_group' => 0,
        ]);

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'col' => 6,
                        'type' => 'text',
                        'prefix' => $this->context->smarty->fetch(
                            $this->getLocalPath() . 'views/templates/admin/shop-icon.tpl'
                        ),
                        'desc' => $this->context->smarty->fetch(
                            $this->getLocalPath() . 'views/templates/admin/shop-id-desc.tpl'
                        ),
                        'hint' => $this->context->smarty->fetch(
                            $this->getLocalPath() . 'views/templates/admin/shop-id-desc.tpl'
                        ),
                        'name' => 'SHOPARIZEPARTNER_SHOP_ID',
                        'label' => $this->l('Shoparize Shop ID'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Color attribute'),
                        'name' => 'SHOPARIZEPARTNER_COLOR_ATTR_GROUP',
                        'hint' => $this->l('Select which attribute group is using as color at the shop'),
                        'col' => '4',
                        'default_value' => (int) Configuration::get('SHOPARIZEPARTNER_COLOR_ATTR_GROUP'),
                        'options' => [
                            'query' => $attributeGroups,
                            'id' => 'id_attribute_group',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Size attribute'),
                        'name' => 'SHOPARIZEPARTNER_SIZE_ATTR_GROUP',
                        'hint' => $this->l('Select which attribute group is using as size at the shop'),
                        'col' => '4',
                        'default_value' => (int) Configuration::get('SHOPARIZEPARTNER_SIZE_ATTR_GROUP'),
                        'options' => [
                            'query' => $attributeGroups,
                            'id' => 'id_attribute_group',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            'SHOPARIZEPARTNER_SHOP_ID' => Configuration::get(
                'SHOPARIZEPARTNER_SHOP_ID',
                null,
                Context::getContext()->shop->id_shop_group,
                Context::getContext()->shop->id
            ),
            'SHOPARIZEPARTNER_COLOR_ATTR_GROUP' => Configuration::get(
                'SHOPARIZEPARTNER_COLOR_ATTR_GROUP',
                null,
                Context::getContext()->shop->id_shop_group,
                Context::getContext()->shop->id
            ),
            'SHOPARIZEPARTNER_SIZE_ATTR_GROUP' => Configuration::get(
                'SHOPARIZEPARTNER_SIZE_ATTR_GROUP',
                null,
                Context::getContext()->shop->id_shop_group,
                Context::getContext()->shop->id
            ),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        Configuration::updateValue(
            'SHOPARIZEPARTNER_SHOP_ID',
            Tools::getValue('SHOPARIZEPARTNER_SHOP_ID'),
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );
        Configuration::updateValue(
            'SHOPARIZEPARTNER_COLOR_ATTR_GROUP',
            Tools::getValue('SHOPARIZEPARTNER_COLOR_ATTR_GROUP'),
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );
        Configuration::updateValue(
            'SHOPARIZEPARTNER_SIZE_ATTR_GROUP',
            Tools::getValue('SHOPARIZEPARTNER_SIZE_ATTR_GROUP'),
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );

        if (empty($this->context->controller->errors)) {
            $this->context->controller->confirmations[] = $this->l('update successful');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        // force to only load on front office
        if (
            'front' == $this->context->controller->controller_type
            || 'modulefront' == $this->context->controller->controller_type
        ) {
            // get shop id
            $SHOPARIZEPARTNER_SHOP_ID = Configuration::get(
                'SHOPARIZEPARTNER_SHOP_ID',
                null,
                Context::getContext()->shop->id_shop_group,
                Context::getContext()->shop->id
            );
            // assign vars to js files
            Media::addJsDef(
                [
                    'SHOPARIZEPARTNER_SHOP_ID' => $SHOPARIZEPARTNER_SHOP_ID,
                ]
            );
            // include js file to head of the page
            $this->context->controller->registerJavascript(
                'shoparize_script',
                'https://partner-cdn.shoparize.com/js/shoparize.js',
                [
                    'server' => 'remote',
                    'position' => 'head',
                ]
            );

            $this->context->controller->registerJavascript(
                'shoparize_script_all_front',
                'modules/' . $this->name . '/views/js/shoparizepartner_all_front.js'
            );

            if ('orderconfirmation' == Dispatcher::getInstance()->getController()
                || 'order-confirmation' == Dispatcher::getInstance()->getController()) {
                // get order
                $id_order = Tools::getValue('id_order');
                $order = new Order($id_order, $this->context->language->id);
                // get order products
                $orderProducts = $order->getProductsDetail();
                $currencyIsoCode = (new Currency($order->id_currency, $this->context->language->id))->iso_code;

                // hold data for js object
                $orderDetail = [];
                $orderDetail['event'] = 'purchase';
                $orderDetail['ecommerce'] = [
                    'transaction_id' => $order->id,
                    'value' => Tools::ps_round($order->total_paid_tax_incl, 2),
                    'tax' => Tools::ps_round($order->total_paid_tax_incl - $order->total_paid_tax_excl, 2),
                    'shipping' => Tools::ps_round($order->total_shipping_tax_incl, 2),
                    'currency' => $currencyIsoCode,
                ];
                // add products details
                foreach ($orderProducts as $item) {
                    $orderDetail['ecommerce']['items'][] = [
                        'item_id' => $item['product_id'],
                        'item_name' => addslashes($item['product_name']),
                        'currency' => $currencyIsoCode,
                        'price' => Tools::ps_round($item['total_price_tax_excl'], 2),
                        'quantity' => $item['product_quantity'],
                    ];
                }

                // assign vars to js files
                Media::addJsDef(
                    [
                        'order_details_object' => $orderDetail,
                    ]
                );

                $this->context->controller->registerJavascript(
                    'shoparizepartner_order_confirmation',
                    'modules/' . $this->name . '/views/js/shoparizepartner_order_confirmation.js'
                );
            }
        }
    }

    public function hookModuleRoutes()
    {
        return [
            'module-shoparizepartner-get-feed' => [
                'rule' => 'shoparize-partner/products',
                'keywords' => [],
                'controller' => 'feeds',
                'params' => [
                    'fc' => 'module',
                    'module' => 'shoparizepartner',
                ],
            ],
        ];
    }
}
