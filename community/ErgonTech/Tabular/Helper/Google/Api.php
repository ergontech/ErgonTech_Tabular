<?php

class ErgonTech_Tabular_Helper_Google_Api extends Mage_Core_Helper_Abstract
{
    const CONFIG_PATH_API_KEY = 'tabular/google_api/api_key';
    const CONFIG_PATH_API_TYPE = 'tabular/google_api/type';

    /**
     * @var Google_Client
     */
    protected $client;

    /**
     * @param string $serviceClassname
     * @return Google_Service
     */
    public function getService($serviceClassname)
    {
        if (!class_exists($serviceClassname)) {
            throw new ErgonTech_Tabular_Exception_Google_Api('The service ' . $serviceClassname . ' does not exist.');
        }

        return new $serviceClassname($this->getGoogleClient());
    }

    /**
     * @return Google_Client
     */
    public function getGoogleClient()
    {
        if (is_null($this->client)) {
            $type = Mage::getStoreConfig(static::CONFIG_PATH_API_TYPE);
            $apiKey = Mage::getStoreConfig(static::CONFIG_PATH_API_KEY);
            $this->client = Mage::getSingleton(Google_Client::class);
            if ($type === ErgonTech_Tabular_Model_Source_Google_Api_Type::API_KEY) {
                $this->client->setDeveloperKey($apiKey);
            } else {
                $this->client->setAuthConfig($apiKey);
            }
        }

        return $this->client;
    }


}
