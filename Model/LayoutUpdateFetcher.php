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

    public function fetchDbLayoutHandlers(array $handler, string $themeId, string $storeId): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            ['layout_update' => $connection->getTableName('layout_update')],
            ['handle']
        )->join(
            ['link' => $connection->getTableName('layout_link')],
            'link.layout_update_id=layout_update.layout_update_id',
            ''
        )->where(
            'link.store_id IN (0, ?)',
            $storeId
        )->where(
            'link.theme_id = ?',
            $themeId
        )->where(
            'handle IN (?)',
            $handler
        )->order(
            'layout_update.sort_order ' . Select::SQL_ASC
        );

        return $connection->fetchCol($select);
    }
}
