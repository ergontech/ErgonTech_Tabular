<?php

namespace ErgonTech\Tabular;

use Mage;

/**
 * Class Block_Adminhtml_Form_Element_Widget_Select
 * @package ErgonTech\Tabular
 * @method array getValues()
 */
class Block_Adminhtml_Form_Element_Widget_Select extends \Varien_Data_Form_Element_Select
{
    protected $_widgetConfigs = [];

    protected function _prepareOptions()
    {
        parent::_prepareOptions();
        $this->_widgetConfigs = array_reduce(array_keys($this->getOptions()), function ($types, $type) {
            $widgetParams = Mage::getSingleton('widget/widget')
                ->getConfigAsObject($type)
                    ->getData('parameters');

            if ($widgetParams) {
                $types[$type] = array_map(
                    function ($key) {
                        return str_replace('_', ' ', $key);
                    }, array_keys($widgetParams));
            }

            return $types;
        }, []);
    }

    public function getElementHtml()
    {
        $html = parent::getElementHtml();
        $widgetConfigs = Mage::helper('core')->jsonEncode($this->_widgetConfigs);
        $htmlId = $this->getHtmlId();
        $intro = Mage::helper('ergontech_tabular')->__('Use the following additional columns for this widget type');
        return $html . <<<HTML
<div id="{$htmlId}_info"></div>
<script>
(function () {
    var widgetConfigs = {$widgetConfigs};
    var helperElem = $('{$htmlId}_info');
    function updateHelperText(e) {
        var type = e.target.value;
        if (widgetConfigs[type]) {
            helperElem.update(
                '{$intro}: <strong>' 
                + widgetConfigs[type].join(', ')
                + '</strong>');
        }
    }
    $('{$htmlId}').on('change', updateHelperText);
    updateHelperText({target: $('{$htmlId}')});
}());
</script>
HTML;

    }

}