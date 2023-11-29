<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class CoreStatus extends Model
{
    // TODO: GN: préférer la nouvelle table `core_user_roles`
    public static $USER = 1;
    public static $ADMIN = 2;


    /**
     * Get all the status
     *
     * @return multitype: array
     */
    public function allStatusInfo()
    {
        return [
            ['id' => 1, 'name' => 'user'],
            ['id' => 2, 'name' => 'admin']
        ];
    }

}
