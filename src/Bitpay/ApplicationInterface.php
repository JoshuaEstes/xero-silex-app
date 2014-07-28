<?php

/**
 * Creates an application for a new merchant account
 */
interface ApplicationInterface
{

    /**
     * @return array
     */
    public function getUsers();

    /**
     * @return array
     */
    public function getOrgs();
}
