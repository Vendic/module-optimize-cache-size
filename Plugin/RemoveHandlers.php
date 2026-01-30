<?php

/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

declare(strict_types=1);

namespace Vendic\OptimizeCacheSize\Plugin;

use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Widget\Model\ResourceModel\Layout\Update\CollectionFactory;
use Vendic\OptimizeCacheSize\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;

class RemoveHandlers
{
    private const string PRODUCT_ID_HANDLER_STRING = 'catalog_product_view_id_';
    private const string PRODUCT_SKU_HANDLER_STRING = 'catalog_product_view_sku_';
    private const string PRODUCT_ATTRIBUTE_SET_HANDLER_STRING = 'catalog_product_view_attribute_set_';
    private const string CATEGORY_ID_HANDLER_STRING = 'catalog_category_view_id_';

    private array $dbLayoutHandlers = [];

    public function __construct(
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly DesignInterface $design,
        private readonly CollectionFactory $layoutUpdateCollectionFactory
    ) {
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function afterAddHandle(
        ProcessorInterface $subject,
        ProcessorInterface $result,
        array|string|null $handleName
    ): ProcessorInterface {
        if (!$this->config->isModuleEnabled()) {
            return $result;
        }

        foreach ($result->getHandles() as $handler) {
            if (
                $this->config->isRemoveCategoryIdHandlers()
                && str_contains($handler, self::CATEGORY_ID_HANDLER_STRING)
                && !$this->hasDbLayoutUpdate($handler)
            ) {
                $result->removeHandle($handler);
                continue;
            }

            if (
                $this->config->isRemoveProductIdHandlers()
                && str_contains($handler, self::PRODUCT_ID_HANDLER_STRING)
                && !$this->hasDbLayoutUpdate($handler)
            ) {
                $result->removeHandle($handler);
                continue;
            }

            if (
                $this->config->isRemoveProductSkuHandlers()
                && str_contains($handler, self::PRODUCT_SKU_HANDLER_STRING)
                && !$this->hasDbLayoutUpdate($handler)
            ) {
                $result->removeHandle($handler);
            }

            if ($this->config->isRemoveProductAttributeSetHandlers()
                && str_contains($handler, self::PRODUCT_ATTRIBUTE_SET_HANDLER_STRING)) {
                $result->removeHandle($handler);
            }
        }
        return $result;
    }

    private function hasDbLayoutUpdate(string $handler): bool
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $themeId = (int)$this->design->getDesignTheme()->getId();

        if (!isset($this->dbLayoutHandlers[$storeId][$themeId][$handler])) {
            $updateCollection = $this->layoutUpdateCollectionFactory->create();
            $updateCollection->addThemeFilter($themeId);
            $updateCollection->addFieldToFilter('store_id', [$storeId, Store::DEFAULT_STORE_ID]);
            $updateCollection->addFieldToFilter('handle', $handler);
            $this->dbLayoutHandlers[$storeId][$themeId][$handler] = $updateCollection->getSize() > 0;
        }

        return $this->dbLayoutHandlers[$storeId][$themeId][$handler];
    }
}
