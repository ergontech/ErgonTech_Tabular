<?php

namespace ErgonTech\Tabular;

use StepExecutionException;

interface Model_Profile_Type
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
     * @param Model_Profile $profile
     * @return void
     */
    public function initialize(Model_Profile $profile);
}
