<?php
declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\OptimizeCacheSize\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const OCS_GENERAL_IS_ENABLED_PATH = 'optimize_cache_size/general/enabled';
    private const OCS_GENERAL_PRODUCT_ID_PATH = 'optimize_cache_size/general/product_id';
    private const OCS_GENERAL_PRODUCT_SKU_PATH = 'optimize_cache_size/general/product_sku';
    private const OCS_GENERAL_PRODUCT_ATTRIBUTE_SET_PATH = 'optimize_cache_size/general/product_attribute_set';
    private const OCS_GENERAL_CATEGORY_ID_PATH = 'optimize_cache_size/general/category_id';

    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isModuleEnabled(int $store = 0): bool
    {
        return  $this->scopeConfig->isSetFlag(
            self::OCS_GENERAL_IS_ENABLED_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isRemoveProductIdHandlers(int $store = 0): bool
    {
        return  $this->scopeConfig->isSetFlag(
            self::OCS_GENERAL_PRODUCT_ID_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isRemoveProductSkuHandlers(int $store = 0): bool
    {
        return  $this->scopeConfig->isSetFlag(
            self::OCS_GENERAL_PRODUCT_SKU_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isRemoveProductAttributeSetHandlers(int $store = 0): bool
    {
        return  $this->scopeConfig->isSetFlag(
            self::OCS_GENERAL_PRODUCT_ATTRIBUTE_SET_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isRemoveCategoryIdHandlers(int $store = 0): bool
    {
        return  $this->scopeConfig->isSetFlag(
            self::OCS_GENERAL_CATEGORY_ID_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
