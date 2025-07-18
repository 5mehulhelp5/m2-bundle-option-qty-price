<?php

declare(strict_types=1);

namespace Infrangible\BundleOptionQtyPrice\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @throws \Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        $optionTableName = $connection->getTableName('catalog_product_option');

        if (! $connection->tableColumnExists(
            $optionTableName,
            'bundle_option_qty_price'
        )) {
            $connection->addColumn(
                $optionTableName,
                'bundle_option_qty_price',
                [
                    'type'     => Table::TYPE_INTEGER,
                    'length'   => 10,
                    'nullable' => true,
                    'comment'  => 'Bundle Option Qty Price'
                ]
            );
        }

        $setup->endSetup();
    }
}
