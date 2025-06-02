<?php

namespace Vendic\OptimizeCacheSize\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class CategoryList implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    public function __construct(
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Return array of ['value' => <category_id>, 'label' => <category_name>]
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('name')
            ->addIsActiveFilter()
            ->addAttributeToSort('name', 'ASC');

        $options = [];
        foreach ($collection as $category) {
            $options[] = [
                'value' => $category->getId(),
                'label' => $category->getName()
            ];
        }

        return $options;
    }
}
