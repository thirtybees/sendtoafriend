<?php
/**
 * Copyright (C) 2017-2019 thirty bees
 * Copyright (C) 2007-2016 PrestaShop SA
 *
 * thirty bees is an extension to the PrestaShop software by PrestaShop SA.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017-2019 thirty bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   Academic Free License (AFL 3.0)
 * PrestaShop is an internationally registered trademark of PrestaShop SA.
 */

if (!defined('_TB_VERSION_'))
	exit;

class sendToAFriend extends Module
{
	private $_html = '';
	private $_postErrors = array();
	public $context;

	function __construct($dontTranslate = false)
 	{
 	 	$this->name = 'sendtoafriend';
		$this->version = '2.0.2';
		$this->author = 'thirty bees';
 	 	$this->tab = 'front_office_features';
		$this->need_instance = 0;
		$this->secure_key = Tools::encrypt($this->name);

		parent::__construct();

		if (!$dontTranslate)
		{
			$this->displayName = $this->l('Send to a Friend Module');
			$this->description = $this->l('Allows customers to send a product link to a friend.');
 		}
		$this->tb_versions_compliancy = '> 1.0.0';
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
	}

	public function install()
	{
	 	return (parent::install() && $this->registerHook('extraLeft') && $this->registerHook('header'));
	}

	public function uninstall()
	{
		return (parent::uninstall() && $this->unregisterHook('header') && $this->unregisterHook('extraLeft'));
	}

	public function hookExtraLeft($params)
	{
		/* Product informations */
		$product = new Product((int)Tools::getValue('id_product'), false, $this->context->language->id);
		$image = Product::getCover((int)$product->id);


		$this->context->smarty->assign(array(
			'stf_product' => $product,
			'stf_product_cover' => (int)$product->id.'-'.(int)$image['id_image'],
			'stf_secure_key' => $this->secure_key
		));

		return $this->display(__FILE__, 'sendtoafriend-extra.tpl');
	}

	public function hookHeader($params)
	{
		$this->page_name = Dispatcher::getInstance()->getController();
		if ($this->page_name == 'product')
		{
			$this->context->controller->addCSS($this->_path.'sendtoafriend.css', 'all');
			$this->context->controller->addJS($this->_path.'sendtoafriend.js');
		}
	}

	public function isValidName($name)
	{
		$isName          = Validate::isName($name);
		$isShortName     = $this->isShortName($name);
		$isNameLikeAnUrl = $this->isNameLikeAnUrl($name);
		$isValidName     = $isName && $isShortName && !$isNameLikeAnUrl;

		return $isValidName;
	}

	public function isShortName($name)
	{
		$isShortName = (strlen($name) <= 50);

		return $isShortName;
	}

	public function isNameLikeAnUrl($name)
	{
		// THIS REGEX IS NOT MEANT TO FIND A VALID URL
		// the goal is to see if the given string for a Person Name is containing something similar to an url
		//
		// See all strings that i tested the regex against in https://regex101.com/r/yL7lU0/3
		//
		// Please fork the regex if you can improve it and make a Pull Request
		$regex           = "/(https?:[\/]*.*)|([\.]*[[[:alnum:]]+\.[^ ]]*.*)/m";
		$isNameLikeAnUrl = (bool) preg_match_all($regex, $name);

		return $isNameLikeAnUrl;
	}
}
