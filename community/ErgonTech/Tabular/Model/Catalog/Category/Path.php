<?php

namespace ErgonTech\Tabular;

use Mage;
use ReflectionProperty;

class Model_Catalog_Category_Path extends \Mage_Core_Model_Abstract
{
    /**
     * @var array
     */
    protected static $categoriesByNamePath = null;

    /**
     * Handle a special case: load by name_path
     * @param int $id
     * @param null $field
     * @return bool|\Mage_Core_Model_Abstract
     */
    public function load($id, $field = null)
    {
        if ($field === 'name_path') {
            return $this->loadByNamePath($id);
        }
        return Mage::getModel('catalog/category')->loadByAttribute($field, $id);
    }

    /**
     * Using an array in the style defined by FastSimpleImport, try to load a category by *name* path
     *
     * @param $fullNamePath
     * @return bool|\Mage_Core_Model_Abstract
     */
    protected function loadByNamePath($fullNamePath)
    {
        $categoriesByPath = $this->getCategoriesByNamePath();
        @list($root, $path) = explode('/', $fullNamePath, 2);

        if (isset($categoriesByPath[$root][$path])) {
            return Mage::getModel('catalog/category')->load($categoriesByPath[$root][$path]['entity_id']);
        }

        return false;
    }

    /**
     * pull category structure out of FastSimpleImport's product categorization
     *
     * @return array
     */
    protected function getCategoriesByNamePath()
    {
        if (is_null($this->categoriesByNamePath)) {
            // Cheat a little bit
            // I have no interest in copying the entire block of code that generates `_categoriesWithRoots`
            // So we use reflection to pull it out
            $catImport = Mage::getSingleton('fastsimpleimport/import_entity_category_product');
            $ref = new ReflectionProperty($catImport, '_categoriesWithRoots');
            $ref->setAccessible(true);
            static::$categoriesByNamePath = $ref->getValue($catImport);
        }

        return static::$categoriesByNamePath;
    }

}