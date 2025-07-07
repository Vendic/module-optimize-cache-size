<?php

/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

declare(strict_types=1);

namespace Vendic\OptimizeCacheSize\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

readonly class LayoutUpdateFetcher
{
    public function __construct(
        private ResourceConnection $resourceConnection
    ) {
    }

    public function isDbLayoutHandler(string $handler, string $themeId, string $storeId): bool
    {
        $connection = $this->resourceConnection->getConnection();

        $bind = [
            'theme_id' => $themeId,
            'store_id' => $storeId,
            'handle' => $handler,
        ];

        $select = $connection->select()->from(
            ['layout_update' => $connection->getTableName('layout_update')],
            ['handle']
        )->join(
            ['link' => $connection->getTableName('layout_link')],
            'link.layout_update_id=layout_update.layout_update_id',
            ''
        )->where(
            'link.store_id IN (0, :store_id)'
        )->where(
            'link.theme_id = :theme_id'
        )->where(
            'handle = :handle'
        )->order(
            'layout_update.sort_order ' . Select::SQL_ASC
        );

        return (bool)$connection->fetchOne($select, $bind);
    }
}
