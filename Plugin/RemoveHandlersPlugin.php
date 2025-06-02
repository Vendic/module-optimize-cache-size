<?php
declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\OptimizeCacheSize\Plugin;

use Magento\Framework\View\Layout\ProcessorInterface;
use Vendic\OptimizeCacheSize\Model\Config;

class RemoveHandlersPlugin
{

    private const PRODUCT_ID_HANDLER_STRING = 'catalog_product_view_id_';
    private const PRODUCT_SKU_HANDLER_STRING = 'catalog_product_view_sku_';
    private const CATEGORY_ID_HANDLER_STRING = 'catalog_category_view_id_';

    public function __construct(
        private Config $config
    ) {
    }

    public function afterAddHandle(
        ProcessorInterface $subject,
        ProcessorInterface $result,
        array|string $handleName
    ): ProcessorInterface {
        if (!$this->config->isModuleEnabled()) {
            return $result;
        }
        $handlers = $result->getHandles();
        foreach ($handlers as $handler) {
            if ($this->config->isRemoveCategoryIdHandlers()
                && str_contains($handler, self::CATEGORY_ID_HANDLER_STRING)) {

                $categoryID = str_replace(self::CATEGORY_ID_HANDLER_STRING, '', $handler);
                $categoryIdExclusions = $this->config->getCategoryIdExclusions();

                if(!in_array($categoryID, $categoryIdExclusions)){
                    $result->removeHandle($handler);
                    continue;
                }
            }
            if ($this->config->isRemoveProductIdHandlers()
                && str_contains($handler, self::PRODUCT_ID_HANDLER_STRING)) {
                $result->removeHandle($handler);
                continue;
            }
            if ($this->config->isRemoveProductSkuHandlers()
                && str_contains($handler, self::PRODUCT_SKU_HANDLER_STRING)) {
                $result->removeHandle($handler);
            }
        }
        return $result;
    }
}
