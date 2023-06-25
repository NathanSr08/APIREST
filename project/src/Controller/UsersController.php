<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;   
use JMS\Serializer\SerializerInterface; 
use App\Repository\UsersRepository;
use App\Repository\OrderRepository;
use Symfony\Contracts\Cache\ItemInterface;
use App\Entity\Users;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FramworkExtraBundle\Configuration\IsGranted;

class UsersController extends AbstractController
{
    #[Route('/api/toto', name: 'usersCache', methods: ['GET'])]
    public function getAllBooks(UsersRepository $usersRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllBooks-" . $page . "-" . $limit;
        $bookList = $cachePool->get($idCache, function (ItemInterface  $item) use ($usersRepository, $page, $limit) {
            $item->tag("booksCache");
            return $usersRepository->findAllWithPagination($page, $limit);
        });

        $jsonBookList = $serializer->serialize($bookList, 'json', ['groups' => 'getBooks']);
        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
   }

   #[Route('/api/users', name: 'book', methods: ['GET'])]
   public function getUsersList(Request $request,UsersRepository $usersRepository,SerializerInterface $serializer): JsonResponse
   {
       $page = $request->get('page', 1);
       $limit = $request->get('limit', 3);
       $bookList = $usersRepository->findAllWithPagination($page, $limit);
       $context = SerializationContext::create()->setGroups(['getOrders']);
       $jsonBookList = $serializer->serialize($bookList, 'json', $context);
       return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
   }


    #[Route('/api/user/{id}', name: 'users_id', methods: ['GET'])]
    public function getUsersId(Users $users,SerializerInterface $serializer): JsonResponse
    {
        $jsonBook = $serializer->serialize($users, 'json');
        return new JsonResponse($jsonBook, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/user/{id}', name: 'deleteBook', methods: ['DELETE'])]
    public function deleteUsers(Users $users, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($users);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/user', name:"createUsers", methods: ['POST'])] 
    public function createBook(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,UrlGeneratorInterface $urlGenerator,OrderRepository $orderRepository): JsonResponse {
        $users = $serializer->deserialize($request->getContent(), Users::class, 'json');
        $content = $request->toArray();
        $em->persist($users);
        $em->flush();
		$jsonBook = $serializer->serialize($users, 'json', ['groups' => 'getOrder']);
        $location = $urlGenerator->generate('users_id', ['id' => $users->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);	
    }


    #[Route('/api/user/{id}', name:"updateUser", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour éditer un livre')]
    public function updateUser(Request $request, SerializerInterface $serializer, User $currentUser, EntityManagerInterface $em, OrderRepository $orderRepository, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse 
    {
        $newUser = $serializer->deserialize($request->getContent(), User::class, 'json');
        $currentUser->setNom($newUser->getNom());
        $currentUser->setPrenom($newUser->getPrenom());

        // On vérifie les erreurs
        $errors = $validator->validate($currentUser);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $idOrder = $content['idOrder'] ?? -1;
    
        $currentBook->setAuthor($authorRepository->find($idAuthor));

        $em->persist($currentBook);
        $em->flush();

        // On vide le cache.
        $cache->invalidateTags(["booksCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

 
}