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


            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "Email inválido.";
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);

            if($email != null && $password != null && count($validate_email) == 0){

                $jwt_auth = $this->get(JwtAuth::class);

                $credentials = $jwt_auth->signup($email, $password);

                $data = array(
                    'status' => 'success',
                    'credentials' => $credentials
                );
            }else{
                $data = array(
                    'status' => 'error',
                    'email' => 'email incorrect'
                );
            }
        }

        return $helpers->json($data);
    }


    public function testeAction() {

        $em = $this->getDoctrine()->getManager();
        $userRepo = $em -> getRepository('BackendBundle:User');
        $users = $userRepo -> findAll();

        $helpers = $this->get(Helpers::class);

        return $helpers->json(array(
            'status'=> 'success',
            'users'=>  $users
        ));

    }


}
