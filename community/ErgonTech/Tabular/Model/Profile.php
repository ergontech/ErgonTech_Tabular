<?php

namespace ErgonTech\Tabular;

use Mage;
use Mage_Core_Model_Abstract;
use Varien_Object;

/**
 * @method string getProfileType()
 * @method string getName()
 * @method mixed getExtra($key = null) Data provided by the `extra` key of the given `profile_type`
 * @method int getStoreId()
 * @method array getStores()
 */
class Model_Profile extends Mage_Core_Model_Abstract
{
    const MAX_NAME_LENGTH = 255;

    /**
     * @var string
     */
    protected $_resourceName = 'ergontech_tabular/profile';

    /**
     * @var string
     */
    protected $_resourceCollectionName = 'ergontech_tabular/profile_collection';

    /**
     * @var array
     */
    private $validations;

    /**
     * Pass data hash through setData to retroactively validate data
     */
    public function __construct($args = [])
    {
        parent::__construct($args);
        $this->setData($this->getData());

        $this->validations = [
            'name' => function ($name) {
                return is_string($name) && strlen($name) <= static::MAX_NAME_LENGTH;
            },
            'type_id' => function ($type_id) {
                $types = Mage::getModel('ergontech_tabular/source_profile_type')->getProfileTypes();

                return array_key_exists($type_id, $types);
            }
        ];
    }

    /**
     * @param array|string $key
     * @param null $value
     * @return $this|Varien_Object
     * @throws Exception_Profile
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setData($k, $v);
            }

            return $this;
        }

        $validationExists = array_key_exists($key, $this->validations);
        if (!$validationExists || ($validationExists && call_user_func($this->validations[$key], $value))) {
            return parent::setData($key, $value);
        }

        throw new Exception_Profile("{$key} could not be validated with the given value \"{$value}\"");

    }

    /**
     * Load a profile with the given name
     *
     * @param $name
     * @return Model_Profile
     */
    public function loadByName($name)
    {
        return Mage::getModel('ergontech_tabular/profile')->load($name, 'name');
    }
}
