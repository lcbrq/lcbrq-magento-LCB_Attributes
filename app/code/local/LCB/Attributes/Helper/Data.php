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
}
	 
