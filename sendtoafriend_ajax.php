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
 * https://opensource.org/licenses/afl-3.0.php
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

/** @noinspection PhpUnhandledExceptionInspection */
require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');

if (!Module::isEnabled('sendtoafriend')) {
    die('0');
}

if (Tools::getValue('action') !== 'sendToMyFriend') {
    die('0');
}

/** @var sendToAFriend $module */
$module = Module::getInstanceByName('sendtoafriend');
if (!$module) {
    die('0');
}

if (Tools::getValue('secure_key') === $module->getSecureKey()) {
    // Retrocompatibilty with old theme
    if ($friend = Tools::getValue('friend')) {
        $data = json_decode($friend, true);
        $friendName = $data['friend_name'] ?? '';
        $friendMail = $data['friend_email'] ?? '';
        $id_product = $data['id_product'];
    } else {
        $friendName = Tools::getValue('name');
        $friendMail = Tools::getValue('email');
        $id_product = Tools::getValue('id_product');
    }

    if (!$friendName || !$friendMail || !$id_product) {
        die('0');
    }

    if (!Validate::isEmail($friendMail) || !$module->isValidName($friendName)) {
        die('0');
    }

    $context = Context::getContext();
    $languageId = $context->language->id;
    $customer = $context->customer;
    $link = $context->link;

    /* Email generation */
    $product = new Product((int)$id_product, false, $languageId);
    $productLink = $link->getProductLink($product);
    $customer = Validate::isLoadedObject($customer)
        ? $customer->firstname . ' ' . $customer->lastname
        : $module->l('A friend', 'sendtoafriend_ajax');

    $templateVars = array(
        '{product}' => $product->name,
        '{product_link}' => $productLink,
        '{customer}' => $customer,
        '{name}' => Tools::safeOutput($friendName)
    );

    /* Email sending */
    if (Mail::Send(
        $languageId,
        'send_to_a_friend',
        sprintf(Mail::l('%1$s sent you a link to %2$s', $languageId), $customer, $product->name),
        $templateVars,
        $friendMail,
        null,
        null,
        null,
        null,
        null,
        dirname(__FILE__) . '/mails/'
    )) {
        die('1');
    }
}

die('0');
