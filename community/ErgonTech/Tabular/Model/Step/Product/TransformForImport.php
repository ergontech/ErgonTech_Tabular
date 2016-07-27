<?php

use ErgonTech\Tabular\TransformStep;

class ErgonTech_Tabular_Model_Step_Product_TransformForImport extends TransformStep
{

    public function __construct()
    {
        parent::__construct([]);
    }

    /**
     * Transform the
     * @param $header
     * @return mixed
     */
    public function getMappedColumnHeader($header)
    {
        return str_replace(' ', '_',  strtolower($header));
    }
}
