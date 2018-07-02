<?php

namespace AppBundle\Controller;

use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }


    public function loginAction(Request $request) {
        $helpers = $this->get(Helpers::class);

        $data = array(
            'status' => 'error'
        );

        //Receber json por POST
        $json = $request->get('json', null);

        if($json != null){

            $params = json_decode($json);

            $email = isset($params->email) ? $params->email: null;
            $password = isset($params->password) ? $params->password: null;
            $getHash = isset($params->getHash) ? $params->getHash: null;


            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This email is not valid.";
            $validateEmail = $this->get("validator")->validate($email, $emailConstraint);
            $pwd = hash('sha256', $password);


            if($email != null && $password != null && count($validateEmail) == 0){

                $jwtAuth = $this->get(JwtAuth::class);

                if ($getHash == null || $getHash == false) {
                    $signup = $jwtAuth->signup($email, $pwd);
                }else {
                    $signup = $jwtAuth->signup($email, $pwd, true);
                }
                return  $helpers->json($signup);

            }else{
                $data = array(
                    'status' => 'error',
                    'data' => 'email incorrect'
                );
            }
        }

        return $helpers->json($data);
    }


    public function provasAction(Request $request) {

        $token = $request->get('authorization', null);

        $helpers = $this->get(Helpers::class);
        $jwtAuth = $this->get(JwtAuth::class);

        if ($token && $jwtAuth->checkToken($token)){
            $em = $this->getDoctrine()->getManager();
            $userRepo = $em -> getRepository('BackendBundle:User');
            $users = $userRepo -> findAll();


            return $helpers->json(array(
                'status'=> 'success',
                'users'=>  $users
            ));
        }else {
            return $helpers->json(array(
                'status'=> 'error',
                'code'=> '400',
                'data'=>  'Authorization not valid.'
            ));
        }
    }


}

