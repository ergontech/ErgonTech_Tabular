<?php

/**
 * @method string getType()
 * @method string getName()
 * @method mixed getExtra($key = null)
 */
class ErgonTech_Tabular_Model_Import_Profile extends Mage_Core_Model_Abstract
{
    const MAX_NAME_LENGTH = 255;

    const XML_PATH_PROFILE_TYPE = 'ergontech/tabular/import/profile/type';

    /**
     * @var string
     */
    protected $_resourceName = 'ergontech_tabular/import_profile';

    /**
     * @var string
     */
    protected $_resourceCollectionName = 'ergontech_tabular/import_profile_collection';

    /**
     * @var array
     */
    private $validations;

    /**
     * Pass data hash through setData to retroactively validate data
     */
    public function __construct()
    {
        parent::__construct();
        $this->setData($this->getData());

        $this->validations = [
            'name' => function ($name) {
                return is_string($name) && strlen($name) <= static::MAX_NAME_LENGTH;
            },
            'type_id' => function ($type_id) {
                $types = Mage::getConfig()->getNode(static::XML_PATH_PROFILE_TYPE)->asArray();

                return array_key_exists($type_id, $types);
            }
        ];
    }

    /**
     * @param array|string $key
     * @param null $value
     * @return $this|Varien_Object
     * @throws ErgonTech_Tabular_Exception_Import_Profile
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setData($k, $v);
            }

            return $this;
        }

        if (array_key_exists($key, $this->validations) && call_user_func($this->validations[$key], $value)) {
            return parent::setData($key, $value);
        }

        throw new ErgonTech_Tabular_Exception_Import_Profile("{$key} could not be validated with the given value \"{$value}\"");

    }

    /**
     * Load a profile with the given name
     *
     * @param $name
     * @return ErgonTech_Tabular_Model_Import_Profile
     */
    public function loadByName($name)
    {
        return Mage::getModel('ergontech_tabular/import_profile')->load($name, 'name');
    }
}
