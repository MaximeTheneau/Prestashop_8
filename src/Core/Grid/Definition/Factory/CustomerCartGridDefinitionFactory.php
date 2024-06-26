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

namespace PrestaShop\PrestaShop\Core\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\ViewOptionsCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DateTimeColumn;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;

/**
 * Class CustomerCartGridDefinitionFactory defines customer's cart grid structure.
 */
final class CustomerCartGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    use DeleteActionTrait;

    public const GRID_ID = 'customer_cart';

    /**
     * @var string
     */
    private $contextDateFormat;

    /**
     * @param HookDispatcherInterface $hookDispatcher
     * @param string $contextDateFormat
     */
    public function __construct(
        HookDispatcherInterface $hookDispatcher,
        $contextDateFormat
    ) {
        parent::__construct($hookDispatcher);
        $this->contextDateFormat = $contextDateFormat;
    }

    /**
     * {@inheritdoc}
     */
    protected function getId()
    {
        return self::GRID_ID;
    }

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return $this->trans('Carts', [], 'Admin.Global');
    }

    /**
     * {@inheritdoc}
     */
    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add((new DataColumn('id_cart'))
                ->setName($this->trans('ID', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'id_cart',
                ])
            )
            ->add((new DateTimeColumn('date_add'))
                ->setName($this->trans('Date', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'date_add',
                    'format' => $this->contextDateFormat,
                    'clickable' => true,
                ])
            )
            ->add((new DataColumn('carrier_name'))
                ->setName($this->trans('Carrier', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'carrier_name',
                ])
            )
            ->add((new DataColumn('total'))
                ->setName($this->trans('Total (Tax incl.)', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'total',
                    'sortable' => false,
                ])
            )
            ->add((new ActionColumn('actions'))
                ->setName($this->trans('Actions', [], 'Admin.Global'))
                ->setOptions([
                    'actions' => (new RowActionCollection())
                        ->add((new LinkRowAction('view'))
                            ->setName($this->trans('View', [], 'Admin.Actions'))
                            ->setIcon('zoom_in')
                            ->setOptions([
                                'route' => 'admin_carts_view',
                                'route_param_name' => 'cartId',
                                'route_param_field' => 'id_cart',
                                'use_inline_display' => true,
                                'clickable_row' => true,
                            ])
                        ),
                ])
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getViewOptions()
    {
        return (new ViewOptionsCollection())
            ->add('display_name', false);
    }
}
