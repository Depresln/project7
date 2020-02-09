<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;

/**
 * @Route("/api")
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST"})
     * @SWG\Tag(name="Register and Log in")
     * @SWG\Response(
     *     response=200,
     *     description="Creates a new user",
     *     @SWG\Schema(
     *         type="array",
     *         example={"username": "userN", "password": "pass"},
     *         @SWG\Items(ref=@Model(type=User::class, groups={"full"}))
     *     )
     * )
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $values = json_decode($request->getContent());
        if(isset($values->username, $values->password)) {
            $user = new User();
            $user->setUsername($values->username);
            $user->setPassword($passwordEncoder->encodePassword($user, $values->password));
            $user->setRoles($user->getRoles());
            $errors = $validator->validate($user);
            if(count($errors)) {
                $errors = $serializer->serialize($errors, 'json');
                return new Response($errors, 500, [
                    'Content-Type' => 'application/json'
                ]);
            }
            $entityManager->persist($user);
            $entityManager->flush();

            $data = [
                'status' => 201,
                'message' => 'User has been added'
            ];

            return new JsonResponse($data, 201);
        }

        $data = [
            'status' => 500,
            'message' => 'You need to fill the fields username and password'
        ];
        return new JsonResponse($data, 500);
    }

    /**
     * @Route("/login_check", name="login_check", methods={"POST"})
     * @SWG\Tag(name="Register and Log in")
     * @SWG\Response(
     *     response=200,
     *     description="Get authentication token",
     *     @SWG\Schema(
     *         type="array",
     *         example={"username": "user1", "password": "pass"},
     *         @SWG\Items(ref=@Model(type=User::class, groups={"full"}))
     *     )
     * )
     */
    public function login_check(){

    }
}
