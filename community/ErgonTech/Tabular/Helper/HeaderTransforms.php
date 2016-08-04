<?php

class ErgonTech_Tabular_Helper_HeaderTransforms extends Mage_Core_Helper_Abstract
{
    public static function spacesToUnderscoresAndLowercase($input)
    {
        return str_replace(' ', '_', strtolower($input));
    }
}
