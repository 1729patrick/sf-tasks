<?php

namespace  AppBundle\Controller;


use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;
use BackendBundle\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraint;

class TaskController extends Controller{

    public function newAction(Request $request, $id = null) {

        $helpers = $this->get(Helpers::class);

        $jwtAuth = $this->get(JwtAuth::class);

        $token = $request->get('authorization',null);

        $authCheck = $jwtAuth->checkToken($token);

        if ($authCheck) {
            $identity = $jwtAuth->checkToken($token, true);
            $json = $request->get('json', null);

            if ($json != null){
                $params = json_decode($json);

                $createdAt = new \DateTime('now');
                $updatedAt = new \DateTime('now');

                $userId = ($identity->sub != null)? $identity->sub: null;
                $title = isset($params->title)? $params->title: null;
                $description = isset($params->description)? $params->description: null;
                $status = isset($params->status)? $params->status: null;


                if ($userId != null && $title != null) {

                    $em = $this->getDoctrine()->getManager();

                    $user = $em ->getRepository('BackendBundle:User')->findOneBy(array(
                        "id"=> $identity->sub
                    ));

                    if ($id == null){
                        $task = new Task();
                        $task->setCreatedAt($createdAt);
                        $task->setUpdatedAt($updatedAt);
                        $task->setUser($user);
                        $task->setTitle($title);
                        $task->setDescription($description);
                        $task->setStatus($status);

                        $em->persist($task);
                        $em->flush();

                        $data = array(
                            "status"=> "success",
                            "code"=> 200,
                            "data"=> $task
                        );

                    }else {
                        $task = $em->getRepository('BackendBundle:Task')->findOneBy(array(
                            "id"=> $id
                        ));

                        if (isset($identity->sub) && $identity->sub == $task->getUser()->getId()){

                            $task->setUpdatedAt($updatedAt);
                            $task->setTitle($title);
                            $task->setDescription($description);
                            $task->setStatus($status);

                            $em->persist($task);
                            $em->flush();


                            $data = array(
                                "status"=> "success",
                                "code"=> 200,
                                "data"=> $task
                            );

                        }else {
                            $data = array(
                                "status"=> "error",
                                "code"=> 400,
                                "msg"=> "Task update error, you not owner."
                            );
                        }
                    }
                }else {
                    $data = array(
                        "status"=> "error",
                        "code"=> 400,
                        "msg"=> "Task not created, validation failed."
                    );
                }

            }else {

                $data = array(
                    "status"=> "error",
                    "code"=> 400,
                    "msg"=> "Task not created, params failed."
                );
            }
        }else {
            $data = array(
                "status"=> "error",
                "code"=> 400,
                "msg"=> "Authorization not valid."
            );

        }

        return $helpers->json($data);

    }

    public function tasksAction(Request $request) {

        $helpers = $this->get(Helpers::class);

        $jwtAuth = $this->get(JwtAuth::class);

        $token = $request->get('authorization',null);

        $authCheck = $jwtAuth->checkToken($token);

        if ($authCheck) {
            $identity = $jwtAuth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            $dql = 'SELECT t FROM BackendBundle:Task t ORDER BY t.id DESC';

            $query = $em->createQuery($dql);

            $page = $request->query->getInt('page', 1);

            $paginator = $this->get('knp_paginator');

            $itemPerPage = 10;

            $pagination = $paginator->paginate($query, $page, $itemPerPage);

            $totalItemsCount = $pagination->getTotalItemCount();


            $data = array(
                "status"=> "success",
                "code"=> 200,
                "totalItemsCount"=> $totalItemsCount,
                "pageActual" =>$page,
                "itemPerPage" => $itemPerPage,
                "totalPages" => ceil($totalItemsCount / $itemPerPage),
                "data" => $pagination
            );

        }else{

            $data = array(
                "status"=> "error",
                "code"=> 400,
                "msg"=> "Authorization not valid."
            );
        }

        return $helpers->json($data);

    }

    public function taskAction(Request $request, $id = null) {

        $helpers = $this->get(Helpers::class);
        $jwtAuth = $this-> get(JwtAuth::class);

        $token = $request->get('authorization', null);


        $authCheck = $jwtAuth->checkToken($token);

        if ($authCheck){
            $identity = $jwtAuth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            $task = $em->getRepository('BackendBundle:Task')->findOneBy(array(
                "id"=>$id
            ));

            if ($task && is_object($task) && $identity->sub == $task->getUser()->getId()){

                $data = array(
                    "status"=> "success",
                    "code"=> 200,
                    "data"=> $task
                );

            }else {

                $data = array(
                    "status"=> "error",
                    "code"=> 404,
                    "msg"=> "Task not found."
                );

            }

        }else{
            $data = array(
                "status"=> "error",
                "code"=> 400,
                "msg"=> "Authorization not valid."
            );
        }
        return $helpers->json($data);
    }

    public function searchAction(){

        $helpers = $this->get(Helpers::class);
        $jwtAuth = $this-> get(JwtAuth::class);

        $token = $request->get('authorization', null);


        $authCheck = $jwtAuth->checkToken($token);

        if ($authCheck){
            $identity = $jwtAuth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            $filter = $request->get('filter', null);

            if (empty($filter)){
                
            }

        }else {
            $data = array(
                "status"=> "error",
                "code"=> 400,
                "msg"=> "Authorization not valid."
            );

        }
    }
}