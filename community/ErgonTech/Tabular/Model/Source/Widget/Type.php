<?php

namespace ErgonTech\Tabular;

use Mage;

/**
 * @package ErgonTech\Tabular
 */
class Model_Source_Widget_Type
{
    /**
     * @return array
     */
    public function toOptionHash()
    {
        return array_reduce($this->toOptionArray(), function ($carry, $item) {
            return $carry + [$item['value'] => $item['label']];
        }, []);
    }

    public function toOptionArray()
    {
        $widget = Mage::getModel('widget/widget_instance');
        return $widget->getWidgetsOptionArray();
    }
}