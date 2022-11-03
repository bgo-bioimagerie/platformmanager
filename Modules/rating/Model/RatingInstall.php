<?php

require_once 'Framework/Model.php';
require_once 'Modules/rating/Model/Rating.php';

/**
 * Class defining methods to install and initialize the rating database
 *
 */
class RatingInstall extends Model
{
    /**
     * Create the rating database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase()
    {
        $modelCampaign = new RatingCampaign();
        $modelCampaign->createTable();
        $model = new Rating();
        $model->createTable();
    }
}
