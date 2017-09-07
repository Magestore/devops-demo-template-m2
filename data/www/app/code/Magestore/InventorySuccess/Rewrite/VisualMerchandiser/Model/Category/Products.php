<?php


namespace Magestore\InventorySuccess\Rewrite\VisualMerchandiser\Model\Category;
use \Magento\Framework\DB\Select;

$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
$_moduleManager =    $objectManager->create('\Magento\Framework\Module\Manager');
if(!$_moduleManager->isEnabled('Magento_VisualMerchandiser')){
    class BaseProducts {}
}else{
    class BaseProducts extends \Magento\VisualMerchandiser\Model\Category\Products {}
}

class Products extends BaseProducts
{
    /**
     * @param int $categoryId
     * @param int $store
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getCollectionForGrid($categoryId, $store = null)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->getFactory()->create()
            ->getCollection()
            ->addAttributeToSelect([
                'sku',
                'name',
                'price',
                'small_image'
            ]);

        if ($store !== null) {
            $collection->addStoreFilter($store);
        }

        $collection->getSelect()
            ->where('at_position.category_id = ?', $categoryId);

        if ($this->_moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField(
                'stock',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                ['stock_id' => $this->getStockId(),'website_id' => '0'],
                'left'
            );
        }
        $cache = $this->_cache->getPositions($this->_cacheKey);

        if ($cache === false) {
            $collection->joinField(
                'position',
                'catalog_category_product',
                'position',
                'product_id=entity_id',
                null,
                'left'
            );
            $collection->setOrder('position', $collection::SORT_ORDER_ASC);

            // Cache the positions initially
            $_collection = clone $collection;

            $positions = [];
            $idx = 0;
            foreach ($_collection as $item) {
                $positions[$item->getId()] = $idx;
                $idx++;
            }

            $this->_cache->saveData($this->_cacheKey, $positions);
        } else {
            $collection->getSelect()
                ->reset(Select::WHERE)
                ->reset(Select::HAVING);

            $collection->addAttributeToFilter('entity_id', ['in' => array_keys($cache)]);
        }
        return $collection;
    }
}