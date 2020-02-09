<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;

/**
 * @Route("/api", name="client")
 */
class ClientController extends AbstractController
{
    /**
     * @Route("/client/{id}", name="show_client", methods={"GET"})
     * @SWG\Tag(name="Client")
     * @SWG\Response(
     *     response=200,
     *     description="Returns the informations of a client",
     *     @SWG\Schema(
     *         type="array",
     *         example={},
     *         @SWG\Items(ref=@Model(type=Client::class, groups={"full"}))
     *     )
     * )
     */
    public function show(Client $client, ClientRepository $clientRepository, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $client = $clientRepository->find($client->getId());
        $user = $this->getUser();

        if($user->getId() == $client->getUser()->getId()){
            $data = $serializer->serialize($client, 'json', [
                'groups' => ['show']
            ]);

            return new Response($data, 200, [
                'Content-Type' => 'application/json'
            ]);
        } else {
            $data = [
                'status' => 500,
                'message' => 'Access denied'
            ];
            return new JsonResponse($data, 500);
        }
    }

    /**
     * @Route("/clients", name="list_client", methods={"GET"})
     * @Route("/clients/{page}", name="list_client_paginated", methods={"GET"})
     * @SWG\Tag(name="Client")
     * @SWG\Response(
     *     response=200,
     *     description="Returns the list of clients",
     *     @SWG\Schema(
     *         type="array",
     *         example={},
     *         @SWG\Items(ref=@Model(type=Client::class, groups={"full"}))
     *     )
     * )
     */
    public function index(Request $request, SerializerInterface $serializer, int $page = null)
    {
        if(is_null($page) || $page < 1) {
            $page = 1;
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $clients = $this->getUser()->getClients();

        $data = $serializer->serialize($clients, 'json', [
            'groups' => ['list']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/client", name="add_client", methods={"POST"})
     * @SWG\Tag(name="Client")
     * @SWG\Response(
     *     response=200,
     *     description="Add a new client",
     *     @SWG\Schema(
     *         type="array",
     *         example={"first_name": "fname", "last_name": "lname", "email": "example@email.com"},
     *         @SWG\Items(ref=@Model(type=Client::class, groups={"full"}))
     *     )
     * )
     */
    public function add(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $client = $serializer->deserialize($request->getContent(), Client::class, 'json');

        $errors = $validator->validate($client);
        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }

        $client->setUser($this->getUser());
        $entityManager->persist($client);
        $entityManager->flush();
        $data = [
            'status' => 201,
            'message' => 'Client has been added successfully, see it at (GET) /api/client/' . $client->getId()
        ];

        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/client/{id}", name="update_client", methods={"PUT"})
     * @SWG\Tag(name="Client")
     * @SWG\Response(
     *     response=200,
     *     description="Edit an existing client",
     *     @SWG\Schema(
     *         type="array",
     *         example={"email": "newemail@email.com"},
     *         @SWG\Items(ref=@Model(type=Client::class, groups={"full"}))
     *     )
     * )
     */
    public function update(Request $request, SerializerInterface $serializer, Client $client, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $clientUpdate = $entityManager->getRepository(Client::class)->find($client->getId());
        $data = json_decode($request->getContent());

        $errors = $validator->validate($clientUpdate);
        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }

        $user = $this->getUser();

        if($user->getId() == $client->getUser()->getId()) {
            $client->setFirstName($data->first_name);
            $client->setLastName($data->last_name);
            $client->setEmail($data->email);

            $entityManager->flush();

            $data = [
                'status' => 200,
                'message' => 'Client has been updated'
            ];

            return new JsonResponse($data);
        } else {
            $data = [
                'status' => 500,
                'message' => 'Access denied'
            ];
            return new JsonResponse($data);
        }
    }

    /**
     * @Route("/client/{id}", name="delete_client", methods={"DELETE"})
     * @SWG\Tag(name="Client")
     * @SWG\Response(
     *     response=204,
     *     description="Delete an existing client",
     *     @SWG\Schema(
     *         type="array",
     *         example={},
     *         @SWG\Items(ref=@Model(type=Client::class, groups={"full"}))
     *     )
     * )
     */
    public function delete(Client $client, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        if($user->getId() == $client->getUser()->getId()) {
            $entityManager->remove($client);
            $entityManager->flush();
        } else {
            $data = [
                'status' => 500,
                'message' => 'Access denied'
            ];
            return new JsonResponse($data);
        }

        $data = [
            'status' => 204,
            'message' => 'Client successfully deleted'
        ];
        return new JsonResponse($data);
    }
}
