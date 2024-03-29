<?php

namespace ErgonTech\Tabular;

use Google_Client;
use Google_Service;
use Mage;
use Mage_Core_Helper_Abstract;

class Helper_Google_Api extends Mage_Core_Helper_Abstract
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
    public function getService($serviceClassname, array $scopes = [])
    {
        if (!class_exists($serviceClassname)) {
            throw new Exception_Google_Api('The service ' . $serviceClassname . ' does not exist.');
        }

        $client = $this->getGoogleClient();

        if (count($scopes)) {
            $client->setScopes($scopes);
        }

        return new $serviceClassname($client);
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
            if ($type === Model_Source_Google_Api_Type::API_KEY) {
                $this->client->setDeveloperKey($apiKey);
            } else {
                $this->client->setAuthConfig(Mage::helper('core')->jsonDecode($apiKey));
            }
        }

        return $this->client;
    }


}
