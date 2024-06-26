<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShopBundle\Translation\Factory;

use Symfony\Component\Translation\MessageCatalogueInterface;

interface TranslationsFactoryInterface
{
    public const DEFAULT_LOCALE = 'en_US';

    /**
     * Generates extract of global Catalogue, using domain's identifiers.
     *
     * @param string $identifier Domain identifier
     * @param string $locale Locale identifier
     *
     * @return MessageCatalogueInterface
     *
     * @throws ProviderNotFoundException
     */
    public function createCatalogue($identifier, $locale = self::DEFAULT_LOCALE);

    /**
     * Generates Translation tree in Back Office.
     *
     * @param string $domainIdentifier Domain identifier
     * @param string $locale Locale identifier
     * @param null $theme
     * @param null $search
     *
     * @return array Translation tree structure
     *
     * @throws ProviderNotFoundException
     */
    public function createTranslationsArray($domainIdentifier, $locale = self::DEFAULT_LOCALE, $theme = null, $search = null);
}
