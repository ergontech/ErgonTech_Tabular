<?php

use ErgonTech\Tabular\StepExecutionException;

interface ErgonTech_Tabular_Model_Profile_Type
{
    /**
     * Run the profile
     *
     * @return void
     * @throws StepExecutionException
     */
    public function execute();

    /**
     * Initialize the profile type with the given profile instance
     *
     * @param ErgonTech_Tabular_Model_Profile $profile
     * @return void
     */
    public function initialize(ErgonTech_Tabular_Model_Profile $profile);
}
