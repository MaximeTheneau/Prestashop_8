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

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Domain\Product\QueryResult;

/**
 * Holds product details
 */
class ProductDetails
{
    /**
     * @var string
     */
    private $isbn;

    /**
     * @var string
     */
    private $upc;

    /**
     * @var string
     */
    private $gtin;

    /**
     * @var string
     */
    private $mpn;

    /**
     * @var string
     */
    private $reference;

    /**
     * @param string $isbn
     * @param string $upc
     * @param string $gtin
     * @param string $mpn
     * @param string $reference
     */
    public function __construct(
        string $isbn,
        string $upc,
        string $gtin,
        string $mpn,
        string $reference
    ) {
        $this->isbn = $isbn;
        $this->upc = $upc;
        $this->gtin = $gtin;
        $this->mpn = $mpn;
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getIsbn(): string
    {
        return $this->isbn;
    }

    /**
     * @return string
     */
    public function getUpc(): string
    {
        return $this->upc;
    }

    /**
     * @return string
     */
    public function getEan13(): string
    {
        return $this->getGtin();
    }

    public function getGtin(): string
    {
        return $this->gtin;
    }

    /**
     * @return string
     */
    public function getMpn(): string
    {
        return $this->mpn;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }
}
