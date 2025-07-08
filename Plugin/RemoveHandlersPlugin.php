<?php
declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\OptimizeCacheSize\Plugin;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Layout\ProcessorInterface;
use Vendic\OptimizeCacheSize\Model\Config;
use Magento\Widget\Model\ResourceModel\Layout\Update;
use Magento\Framework\Exception\NoSuchEntityException;
use Vendic\OptimizeCacheSize\Model\LayoutUpdateFetcher;

class RemoveHandlersPlugin
{

    private const string PRODUCT_ID_HANDLER_STRING = 'catalog_product_view_id_';
    private const string PRODUCT_SKU_HANDLER_STRING = 'catalog_product_view_sku_';
    private const string CATEGORY_ID_HANDLER_STRING = 'catalog_category_view_id_';

    public function __construct(
        private readonly Config $config,
        private readonly LayoutUpdateFetcher $layoutUpdateFetcher,
        private readonly StoreManagerInterface $storeManager,
        private readonly DesignInterface $design
    ) {
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function afterAddHandle(
        ProcessorInterface $subject,
        ProcessorInterface $result,
        array|string $handleName
    ): ProcessorInterface {
        if (!$this->config->isModuleEnabled()) {
            return $result;
        }

        $store = (string)$this->storeManager->getStore()->getId();
        $theme = (string)$this->design->getDesignTheme()->getId();
        $handlers = $result->getHandles();

        $dbLayoutHandlers = $this->layoutUpdateFetcher->fetchDbLayoutHandlers($handlers, $theme, $store);

        foreach ($handlers as $handler) {
            if (
                $this->config->isRemoveCategoryIdHandlers()
                && str_contains($handler, self::CATEGORY_ID_HANDLER_STRING)
                && !in_array($handler, $dbLayoutHandlers)
            ) {
                $result->removeHandle($handler);
                continue;
            }

            if (
                $this->config->isRemoveProductIdHandlers()
                && str_contains($handler, self::PRODUCT_ID_HANDLER_STRING)
                && !in_array($handler, $dbLayoutHandlers)
            ) {
                $result->removeHandle($handler);
                continue;
            }

            if (
                $this->config->isRemoveProductSkuHandlers()
                && str_contains($handler, self::PRODUCT_SKU_HANDLER_STRING)
                && !in_array($handler, $dbLayoutHandlers)
            ) {
                $result->removeHandle($handler);
            }
        }
        return $result;
    }
}
