<?php

require_once 'abstract.php';

class LCB_Attributes_Shell extends Mage_Shell_Abstract {

    public function run()
    {
        if ($this->getArg('find_usage')) {
            $attributeCode = $this->getArg('find_usage');
            return $this->findAttributeUsage($attributeCode);
        }

        return $this->output($this->usageHelp());
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
    Usage:  php attributes.php -- [options]

  --find_usage {code} - Find attribute usage

USAGE;
    }

    /**
     * Print output
     * 
     * @param string $message
     */
    public function output($message)
    {
        echo "$message\n";
    }

    /**
     * Get attribute usage
     * 
     * @param string $attributeCode
     */
    public function findAttributeUsage($attributeCode)
    {
        $attribute = Mage::getModel('eav/entity_attribute')->getCollection()->addSetInfo()->addFieldToFilter('attribute_code',
                        array('eq' => $attributeCode))->getFirstItem();
        if ($attribute->getAttributeId()) {
            foreach ((array) $attribute->getAttributeSetInfo() as $setId => $setInfo) {
                $set = Mage::getModel('eav/entity_attribute_set')->load($setId);
                $this->output($set->getAttributeSetName());
            }
        } else {
            $this->output(Mage::helper("attributes")->__("Attribute %s was not found",
                            $attributeCode));
        }
    }

}

$shell = new LCB_Attributes_Shell();
$shell->run();
