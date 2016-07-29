<?php

class ErgonTech_Tabular_Helper_Google_Api extends Mage_Core_Helper_Abstract
{

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
            $this->client = new Google_Client();
        }

        return $this->client;
    }


}
