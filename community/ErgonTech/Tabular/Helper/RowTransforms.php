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

    public function widgetLayoutRowTransform(array $row)
    {
        /** @var Model_Profile $this */

        $widgetIds = Helper_RowTransforms::getEntityIdsFromColumn($row['widget'], $this);
        if (empty($widgetIds)) {
            throw new RowValidationException('No valid entities were found from the provided widget references. Do you need to run a widget import first?');
        }
        $widgetId = $widgetIds[0];
        $instance = Mage::getModel('widget/widget_instance', [
            'instance_id' => $widgetId
        ]);
        $instance->getResource()->afterLoad($instance);
        $origPageGroups = $instance->getData('page_groups');
        $pageGroupsTransformed = array_map(function ($pageGroup) {
            return [
                'page_group' => $pageGroup['page_group'],
                $pageGroup['page_group'] => [
                    'page_id' => $pageGroup['page_id'],
                    'instance_id' => $pageGroup['instance_id'],
                    'page_group' => $pageGroup['page_group'],
                    'layout_handle' => $pageGroup['layout_handle'],
                    'block' => $pageGroup['block_reference'],
                    'for' => $pageGroup['page_for'],
                    'entities' => $pageGroup['entities'],
                    'template' => $pageGroup['page_template']
                ]
            ];
        }, $origPageGroups);
        $pageGroupsTransformed[] = [
            'page_group' => $row['page_group'],
            $row['page_group'] => [
                'page_id' => '0',
                'instance_id' => $widgetId,
                'page_group' => $row['page_group'],
                'layout_handle' => $row['layout_handle'],
                'block' => $row['block'],
                'for' => $row['entities'] ? 'specific' : 'all',
                'entities' => implode(',',Helper_RowTransforms::getEntityIdsFromColumn($row['entities'], $this)),
                'template' => $row['template']
            ]
        ];
        return [
            'instance_id' => $widgetId,
            'page_groups' => $pageGroupsTransformed,
            'store_ids' => $row['stores']
        ];
    }

    /**
     * Take a string in the following format:
     * "catalog/product:sku:foo, catalog/product:sku:bar"
     * And return an array of entity IDs:
     * [1,2]
     *
     * @param string $cellValue
     * @param Model_Profile $profile
     * @return array
     */
    public static function getEntityIdsFromColumn($cellValue, Model_Profile $profile)
    {
        // When the cell is empty there's nothing to do
        if (is_null($cellValue)) {
            return [];
        }

        $specificationSeparator = $profile->getExtra('entity_specification_separator') ?: ':';
        $itemSeparator = $profile->getExtra('item_separator') ?: ',';

        // The separated values of this cell. Each one is a
        $values = array_map('trim', explode($itemSeparator, $cellValue));

        // Turn the supplied values into a simple arry of entity IDs
        return array_reduce($values, function ($ids, $value) use($specificationSeparator){

            // i.e. "catalog/product:sku:foo-sku"
            list($type, $attribute, $attributeValue) = explode($specificationSeparator, $value);

            $entity = Mage::getModel($type);

            // catalog EAV models have `loadByAttribute` and can't use `load` like other models
            // *shrug*
            $entity = method_exists($entity, 'loadByAttribute')
                ? $entity = $entity->loadByAttribute($attribute, $attributeValue)
                : $entity = $entity->load($attributeValue, $attribute);

            // If the entity exists, we add its ID to the list, otherwise we keep going
            return !$entity->getId()
                ? $ids
                : array_merge($ids, [$entity->getId()]);
        }, []);
    }

    public function enterpriseBannerRowTransform(array $row)
    {
        /**
         * Row structure
         *
         * name
         * types (csv)
         * default_content
         */
        return [
            'name' => $row['name'],
            'types' => $row['types'],
            'is_enabled' => 1,
            'store_contents' => [
                \Mage_Core_Model_App::ADMIN_STORE_ID => $row['default_content']
            ]
        ];
    }

    public function tabularProfileTransform(array $row)
    {
        /** @var Model_Profile $this */

        $extraFieldKeys = array_keys(
            Mage::getConfig()
                ->getNode(sprintf('%s/%s/extra',
                    Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
                    $this->getProfileType()))
                ->asArray());

        return array_merge(
            $row,
            [
                'extra' => array_reduce($extraFieldKeys, function ($extraFields, $extraFieldKey) use ($row) {
                    return isset($row[$extraFieldKey])
                        ? array_merge($extraFields, [$extraFieldKey => $row[$extraFieldKey]])
                        : $extraFields;
                }, [])
            ]
        );
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