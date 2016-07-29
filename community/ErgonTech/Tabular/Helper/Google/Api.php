<?php

class ErgonTech_Tabular_Helper_Google_Api extends Mage_Core_Helper_Abstract
{
    const CONFIG_PATH_API_KEY = 'tabular/google_api/api_key';

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
            $apiKey = Mage::getStoreConfig(static::CONFIG_PATH_API_KEY);
            $this->client = new Google_Client();
            $this->client->setDeveloperKey($apiKey);
        }

        return $this->client;
    }


}
