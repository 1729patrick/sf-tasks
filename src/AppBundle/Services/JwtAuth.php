<?php
namespace AppBundle\Services;

use Firebase\JWT\JWT;

/**
 * Created by PhpStorm.
 * User: flexprosistemas
 * Date: 29/06/2018
 * Time: 17:45
 */


class JwtAuth{
    public $manager;

    public function __construct($manager){
        $this->manager = $manager;
    }

    public function signup($email, $password){

        return $email . " " . $password;
    }

}