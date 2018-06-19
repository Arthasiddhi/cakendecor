<?php
/**
 * Created by PhpStorm.
 * User: ravi
 * Date: 7/6/18
 * Time: 3:02 PM
 */

namespace CND\Baker\Api\Data;


interface LocationInterface
{
    const LOCATION_ID='location_id';
    const LOCATION_NAME='location_name';


    public function getLocationId();

    /**
     * Set customer id
     *
     * @param int $id
     * @return $this
     */
    public function setLocationId($id);

    public function getLocationName();

    /**
     * Set customer id
     *
     * @param int $id
     * @return $this
     */
    public function setLocationName($name);



}