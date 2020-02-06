<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/api", name="user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/user/{id}", name="show_user", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(User $user, UserRepository $userRepository, SerializerInterface $serializer)
    {
        $user = $userRepository->find($user->getId());
        $data = $serializer->serialize($user, 'json', [
            'groups' => ['show']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/users", name="list_user", methods={"GET"})
     * @Route("/users/{page}", name="list_user_paginated", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(Request $request, UserRepository $userRepository, SerializerInterface $serializer, int $page = null)
    {
        if(is_null($page) || $page < 1) {
            $page = 1;
        }

        $users = $userRepository->findAllUsers($page, $_ENV['LIMIT']);
        $data = $serializer->serialize($users, 'json', [
            'groups' => ['list']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/user/{id}", name="update_user", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(Request $request, SerializerInterface $serializer, User $user, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $userUpdate = $entityManager->getRepository(User::class)->find($user->getId());
        $data = json_decode($request->getContent());

        foreach ($data as $key => $value){
            if($key && !empty($value)) {
                $name = ucfirst($key);
                $setter = 'set'.$name;
                $userUpdate->$setter($value);
            }
        }

        $errors = $validator->validate($userUpdate);
        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }

        $entityManager->flush();
        $data = [
            'status' => 200,
            'message' => 'User has been updated'
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/user/{id}", name="delete_user", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(User $user, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return new Response(null, 204);
    }
}
