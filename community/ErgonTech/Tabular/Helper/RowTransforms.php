<?php

namespace ErgonTech\Tabular;

use Mage;

class Helper_RowTransforms extends \Mage_Core_Helper_Abstract
{
    public function returnSelf($value)
    {
        return $value;
    }

    public function widgetRowTransform(array $row)
    {
        $instanceType = $this->getExtra('widget_type');
        // TODO: This loads from XML config for *every row*.
        //   Quite slow at large N, but it's unlikely we'll ever seen more than 30 rows.
        $widgetConfig = Mage::getSingleton('widget/widget')->getConfigAsObject($instanceType);

        $widgetParameterKeys = array_keys($widgetConfig->getParameters());

        $defaultValues = [
            'store_ids' => $row['stores'] ?: [0],
            'package_theme' => 'base/default'
        ];
        $preferredValues = [
            'instance_type' => $instanceType,
            'widget_parameters' => array_combine(
                $widgetParameterKeys,
                array_map(function ($key) use ($row) {
                    return $row[$key];
                }, $widgetParameterKeys))

        ];

        // Ensure that at least some value for commonly-forgotten columns exists
        // Override potentially incorrect values for instance_type and widget_parameters
        return array_merge($defaultValues, $row, $preferredValues);
    }

    /**
     * Look up in config the row transformation callback configured for this profile
     *
     * @param Model_Profile $profile
     * @return string
     */
    public function getRowTransformCallbackForProfile(Model_Profile $profile)
    {
        $cb = Mage::getConfig()->getNode(sprintf('%s/%s/extra/row_transform_callback/options/%s/callback',
            Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
            $profile->getProfileType(),
            $profile->getExtra('row_transform_callback')));

        list($className, $method) = explode('::', $cb);
        $class = new $className;
        $reflectedCb = new \ReflectionMethod($class, $method);

        return $reflectedCb->getClosure($class)->bindTo($profile);
    }
}