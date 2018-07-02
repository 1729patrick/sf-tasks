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
    public $key;


    public function __construct($manager){
        $this->manager = $manager;

        $this->key = "1MRNCS0D1F7KU252ASSAD2AS84ADG3RKU4I8Y1N25KI*W3DV62SFG5TK58";
    }

    public function signup($email, $password, $getHash = null){

        //verifica se existe o email e senha na tabela
        $user = $this->manager->getRepository('BackendBundle:User')->findOneBy(array(
            'email' => $email,
            'password' => $password
        ));

        $sigup = false;
        if(is_object($user)){

            $token = array(
                "sub" => $user->getId(),
                "email" => $user->getEmail(),
                "name" => $user->getName(),
                "surname" => $user->getSurname(),
                "iat" => time(),     //indice do token
                "exp" => time() + (7 * 24 * 60 * 60)          //expiracao do token    1 semana
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $jwtDecoded = JWT::decode($jwt, $this->key, array('HS256'));

            if ($getHash != null){
                $data = $jwtDecoded;
            }else {
                $data = $jwt;
            }

        }else {
            $data = array(
                "status" => "error",
                "data" => "login failed"
            );
        }

        return $data;
    }


    public function checkToken($jwt, $getIdentity = false){
        $auth = false;

        try{
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));
        }catch (\UnexpectedValueException $e){
            $auth = false;
        }catch (\DomainException $e) {
            $auth = false;
        };

        if (isset($decoded) && is_object($decoded) && isset($decoded-> sub)){
            $auth = true;
        }else {
            $auth = false;
        }

        if ($getIdentity == false) {
            return $auth;
        }else {
            return $decoded;
        }
    }

}