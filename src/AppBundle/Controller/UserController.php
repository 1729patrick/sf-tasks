<?php

namespace  AppBundle\Controller;


use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\User;
use Symfony\Component\Validator\Constraint;


class UserController extends Controller {

    public function newAction(Request $request){

        $helpers = $this->get(Helpers::class);

        $json = $request->get('json', null);

        $params = json_decode($json);

        $data = array(
            "status" => "error",
            "code" => 400,
            'msg' => 'User not created.'
        );

        if ($json != null) {

            $createAd = new \DateTime("now");
            $role = 'user';

            $email = isset($params->email)? $params->email: null;
            $name = isset($params->name)? $params->name: null;
            $surname = isset($params->surname)? $params->surname: null;
            $password = isset($params->password)? $params->password: null;

            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This email is not valid.";
            $validateEmail = $this->get('validator')->validate($email, $emailConstraint);

            if ($email != null  && $name != null && $surname != null && count($validateEmail) == 0){

                $user = new User();
                $user->setCreatedAt($createAd);
                $user->setRole($role);
                $user->setEmail($email);
                $user->setName($name);
                $user->setSurname($surname);

                if ($password !=  null){  //encriptografar senha
                    $pwd = hash('sha256', $password);
                    $user->setPassword($pwd);

                }

                $em = $this->getDoctrine()->getManager();

                $issetUser = $em ->getRepository('BackendBundle:User')->findBy(array( //busca por email enviado para verificar se esse usuário ja existe no db
                    "email" => $email
                ));

                if (count($issetUser) == 0) {
                    $em-> persist($user); //salvar no doctrine
                    $em-> flush(); //salvar no db


                    $data = array(
                        "status" => "success",
                        "code" => 200,
                        'msg' => 'New user created',
                        'user' => $user
                    );

                }else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        'msg' => 'User not created, duplicated'
                    );

                }
            }

        }

        return $helpers->json($data);
    }



    public function editAction(Request $request){

        $helpers = $this->get(Helpers::class);
        $jwtAuth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);

        $authCheck = $jwtAuth->checkToken($token);

        if ($authCheck) {

            $em = $this->getDoctrine()->getManager();

            $identity = $jwtAuth->checkToken($token, true);

            $user = $em->getRepository('BackendBundle:User')->findOneBy(array(
                "id" => $identity-> sub
            ));

            $json = $request->get('json', null);

            $params = json_decode($json);

            $data = array(
                "status" => "error",
                "code" => 400,
                'msg' => 'User not updated.'
            );

            if ($json != null) {

//                $createAd = new \DateTime("now");
                $role = 'user';

                $email = isset($params->email)? $params->email: null;
                $name = isset($params->name)? $params->name: null;
                $surname = isset($params->surname)? $params->surname: null;
                $password = isset($params->password)? $params->password: null;

                $emailConstraint = new Assert\Email();
                $emailConstraint->message = "This email is not valid.";
                $validateEmail = $this->get('validator')->validate($email, $emailConstraint);

                if ($email != null && $password != null && $name != null && $surname != null && count($validateEmail) == 0){

//                    $user->setCreatedAt($createAd);
                    $user->setRole($role);
                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setSurname($surname);

                    //encriptografar senha
                    $pwd = hash('sha256', $password);
                    $user->setPassword($pwd);

                    $issetUser = $em ->getRepository('BackendBundle:User')->findBy(array( //busca por email enviado para verificar se esse usuário ja existe no db
                        "email" => $email
                    ));

                    if (count($issetUser) == 0 || $identity->email == $email) {
                        $em-> persist($user); //salvar no doctrine
                        $em-> flush(); //salvar no db


                        $data = array(
                            "status" => "success",
                            "code" => 200,
                            'msg' => 'User updated.',
                            'user' => $user
                        );

                    }else {
                        $data = array(
                            "status" => "error",
                            "code" => 400,
                            'msg' => 'User not updated.'
                        );

                    }
                }

            }
        }else{
            $data = array(
                "status" => "error",
                "code" => 400,
                'msg' => 'Authorization not valid.'
            );
        }


        return $helpers->json($data);
    }
}