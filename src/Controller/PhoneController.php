<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
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
 * @Route("/api", name="phone")
 */
class PhoneController extends AbstractController
{
    /**
     * @Route("/phone/{id}", name="show_phone", methods={"GET"})
     */
    public function show(Phone $phone, PhoneRepository $phoneRepository, SerializerInterface $serializer)
    {
        $phone = $phoneRepository->find($phone->getId());
        $data = $serializer->serialize($phone, 'json', [
            'groups' => ['show']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/phones", name="list_phone", methods={"GET"})
     * @Route("/phones/{page}", name="list_phone_paginated", methods={"GET"})
     */
    public function index(Request $request, PhoneRepository $phoneRepository, SerializerInterface $serializer, int $page = null)
    {
        if(is_null($page) || $page < 1) {
            $page = 1;
        }

        $phones = $phoneRepository->findAllPhones($page, $_ENV['LIMIT']);
        $data = $serializer->serialize($phones, 'json', [
            'groups' => ['list']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/phone", name="add_phone", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function add(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $phone = $serializer->deserialize($request->getContent(), Phone::class, 'json');

        $errors = $validator->validate($phone);
        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }

        $entityManager->persist($phone);
        $entityManager->flush();
        $data = [
            'status' => 201,
            'message' => 'Phone has been added successfully'
        ];

        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/phone/{id}", name="update_phone", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(Request $request, SerializerInterface $serializer, Phone $phone, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $phoneUpdate = $entityManager->getRepository(Phone::class)->find($phone->getId());
        $data = json_decode($request->getContent());

        foreach ($data as $key => $value){
            if($key && !empty($value)) {
                $name = ucfirst($key);
                $setter = 'set'.$name;
                $phoneUpdate->$setter($value);
            }
        }

        $errors = $validator->validate($phoneUpdate);
        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }

        $entityManager->flush();
        $data = [
            'status' => 200,
            'message' => 'Phone has been updated'
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/phone/{id}", name="delete_phone", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Phone $phone, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($phone);
        $entityManager->flush();

        return new Response(null, 204);
    }
}
