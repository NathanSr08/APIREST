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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class UsersController extends AbstractController
{

     /**
     * Cette méthode permet de récupérer l'ensemble des Users
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des Users",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Users::class, groups={"geB"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Users")
     *
     * @param UsersRepository $usersRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */

//     #[Route('/api/toto', name: 'usersCache', methods: ['GET'])]
//     public function getAllBooks(UsersRepository $usersRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
//     {
//         $page = $request->get('page', 1);
//         $limit = $request->get('limit', 3);

//         $idCache = "getAllBooks-" . $page . "-" . $limit;
//         $bookList = $cachePool->get($idCache, function (ItemInterface  $item) use ($usersRepository, $page, $limit) {
//             $item->tag("booksCache");
//             return $usersRepository->findAllWithPagination($page, $limit);
//         });
//         $context = SerializationContext::create()->setGroups(['getBooks']);
//         $jsonBookList = $serializer->serialize($bookList, 'json', $context);
//         return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
//    }

   #[Route('/api/users', name: 'detailUsers', methods: ['GET'])]
   public function getUsersList(Request $request,UsersRepository $usersRepository,SerializerInterface $serializer): JsonResponse
   {
       $page = $request->get('page', 1);
       $limit = $request->get('limit', 3);
       $bookList = $usersRepository->findAllWithPagination($page, $limit);
       $context = SerializationContext::create()->setGroups(['getOrders']);
       $jsonBookList = $serializer->serialize($bookList, 'json', $context);
       return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
   }

     /**
     * Cette méthode permet de récuperer les info d'un User
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste d'un User Specifique",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Users::class, groups={"geB"}))
     *     )
     *
     * )
     * @OA\Tag(name="Users")
     *
     * @param UsersRepository $usersRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/user/{id}', name: 'users_id', methods: ['GET'])]
    public function getUsersId(Users $users,SerializerInterface $serializer): JsonResponse
    {
        $jsonBook = $serializer->serialize($users, 'json');
        return new JsonResponse($jsonBook, Response::HTTP_OK, ['accept' => 'json'], true);
    }


  /**
     * Cette méthode permet de supprimer un User par rapport à son id. 
     *
     *  @OA\Tag(name="Users")
     * 
     * 
     * @param Users $users
     * @param EntityManagerInterface $em
     * @return JsonResponse 
     */
    #[Route('/api/user/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(Users $users, EntityManagerInterface $em): JsonResponse {
        $em->remove($users);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

      /**
     * Cette méthode permet d'insérer un nouveau Uttilisateur. 
     * Exemple de données : 
     * {
     *     "Nom": "Le Seigneur des Anneaux",
     *     "Prenom": "C'est l'histoire d'un anneau unique", 
     *     "idOrder": 5
     * }
     * 
     * @OA\Tag(name="Users")
     * 
     * Le paramètre idOrder est géré "à la main", pour créer l'association
     * entre un User et un Order. 
     * S'il ne correspond pas à un auteur valide, alors le livre sera considéré comme sans auteur. 
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param AuthorRepository $authorRepository
     * @return JsonResponse
     */
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

 
}
