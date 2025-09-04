<?php

/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

declare(strict_types=1);

namespace Vendic\OptimizeCacheSize\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class LayoutUpdateFetcher
{
    private bool $layoutUpdateTableHasRows;
    private bool $layoutUpdateTableHasRowsCalculated;
    private array $cache;

    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
        $this->layoutUpdateTableHasRows = true;
        $this->layoutUpdateTableHasRowsCalculated = false;
        $this->cache = [];
    }

    private function calculateLayoutUpdateTableHasRows(): void
    {
        $this->layoutUpdateTableHasRowsCalculated = true;
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('layout_update');
        $select = $connection->select()
            ->from($table, ['handle'])
            ->limit(1);
        $this->layoutUpdateTableHasRows = (bool)$connection->fetchOne($select);
    }

    public function fetchDbLayoutHandlers(array $handler, string $themeId, string $storeId): array
    {
        if (!$this->layoutUpdateTableHasRowsCalculated) {
            $this->calculateLayoutUpdateTableHasRows();
        }

        if (!$this->layoutUpdateTableHasRows) {
            return [];
        }

        $cacheKeyGenerator = static function (array $handler, string $themeId, string $storeId): string {
            sort($handler);
            return md5(json_encode([$handler, $themeId, $storeId]));
        };
        $cacheKey = $cacheKeyGenerator($handler, $themeId, $storeId);

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

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

        $this->cache[$cacheKey] = $connection->fetchAll($select);
        return $this->cache[$cacheKey];
    }
}
