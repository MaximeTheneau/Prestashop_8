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

namespace Tests\Unit\PrestaShopBundle;

use PrestaShopBundle\Entity\Translation;
use PrestaShopBundle\Translation\Constraints\PassVsprintf;
use PrestaShopBundle\Translation\Constraints\PassVsprintfValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class PassVsprintfContraintTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new PassVsprintfValidator();
    }

    public function testEmptyTranslationIsValid()
    {
        $translation = (new Translation())
            ->setKey('')
            ->setTranslation('');
        $this->validator->validate($translation, new PassVsprintf());

        $this->assertNoViolation();
    }

    public function testTranslationIsValid()
    {
        $translation = (new Translation())
            ->setKey('List of products by brand %s')
            ->setTranslation('Liste des produits de la marque %s');
        $this->validator->validate($translation, new PassVsprintf());

        $this->assertNoViolation();
    }

    public function testNotValid()
    {
        $translation = (new Translation())
            ->setKey('List of products by brand %s')
            ->setTranslation('Liste des produits de la marque nope');
        $constraint = new PassVsprintf();

        $this->validator->validate($translation, $constraint);

        $this->buildViolation($constraint->message)->assertRaised();
    }
}
