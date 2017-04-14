<?php
class LCB_Attributes_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get attributes from particular group to be shown on product listing or product view
     * @author Jigsaw Marcin Gierus <martin@lcbrq.com>
     * @param Mage_Catalog_Model_Product $_product
     * @param array $groupsToSearch
     */
    public function getAttributesToShow($_product , $groupsToSearch) {
        $product = Mage::getModel('catalog/product')->load($_product->getId());
        $attributeSetId = $product->getAttributeSetId();

        $attributeGroup = Mage::getModel('eav/entity_attribute_group')
                ->getResourceCollection()
                ->setAttributeSetFilter($attributeSetId)
                ->setSortOrder()
                ->load();

        foreach ($attributeGroup as $group) {
            if (in_array($group->getAttributeGroupName(),$groupsToSearch)) {
                $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
                        ->setAttributeGroupFilter($group->getId())
                        ->addVisibleFilter()
                        ->checkConfigurableProducts()
                        ->load();

                return $this->getAttributesFromGroup($attributes, $product);
            }
        }
    }
    
    /**
     * Get attributes array from particular group
     * @param Mage_Catalog_Model_Product_Attribute $attributes
     * @param Mage_Catalog_Model_Product $_product
     * @author Jigsaw Marcin Gierus <martin@lcbrq.com>
     * @return boolean|array
     */
    protected function getAttributesFromGroup($attributes, $_product) {
        $attributesArray = array();
        if ($attributes->getSize() > 0) {
            foreach ($attributes as $attribute) {
                if ($_product->getAttributeText($attribute->getAttributeCode())) {

                    $label = $_product->getResource()->getAttribute($attribute->getAttributeCode())->getStoreLabel();
                    $attributesArray[$label] = $_product->getAttributeText($attribute->getAttributeCode());
                }
            }
            return $attributesArray;
        } else {
            return false;
        }
    }
    
    /**
     * Remove unused product attribute values
     * 
     * @author Tomasz Silpion Gregorczyk <tom@lcbrq.com>
     * @param int $limit
     * @return void
     */
    public function removeUnusedAttributes(int $limit = null)
    {

        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        $products = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
        $config = Mage::getModel('eav/config');

        if ($limit) {
            $products->getSelect()->limit($limit);
        }

        foreach ($products as $product) {

            $resource = Mage::getSingleton('catalog/product')->getResource();

            foreach ($attributes as $attribute) {
                /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
                $code = $attribute->getAttributeCode();

                $assignations = $config->getEntityAttributeCodes(
                        Mage_Catalog_Model_Product::ENTITY, $product
                );

                if (!in_array($code, $assignations)) {
                    $value = $resource->getAttributeRawValue($product->getId(), $code, Mage::app()->getStore());
                    $product->setData($code, false);
                    $resource->saveAttribute($product, $code);
                }
            }
        }
    }

}
	 
