<?php

namespace ErgonTech\Tabular;

class Model_Source_Google_Api_Type
{
    const API_KEY = 'api_key';

    const SERVICE_ACCOUNT = 'service_account';

    public function toOptionArray()
    {
        $helper = Mage::helper('ergontech_tabular');
        return [
            [
                'label' => $helper->__('API key (Public Access)'),
                'value' => self::API_KEY
            ], [
                'label' => $helper->__('Service Account OAuth (Private Access)'),
                'value' => self::SERVICE_ACCOUNT
            ]
        ];
    }
}
