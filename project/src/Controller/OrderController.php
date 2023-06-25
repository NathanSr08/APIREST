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
use App\Repository\OrderRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Repository\UsersRepository;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use  Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class OrderController extends AbstractController
{
    #[Route('/api/orders', name: 'order', methods: ['GET'])]
    public function getOrdersList(Request $request,OrderRepository $orderRepository, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $orderList = $orderRepository->findAllWithPagination($page, $limit);
        $context = SerializationContext::create()->setGroups(['getOrders']);
        $jsonOrderList = $serializer->serialize($orderList, 'json',$context);
        return new JsonResponse($jsonOrderList, Response::HTTP_OK, [], true);
    }
    #[Route('/api/order/{id}', name: 'order_id', methods: ['GET'])]
    public function getOrderId(Order $order,SerializerInterface $serializer): JsonResponse
    {
        $jsonOrder = $serializer->serialize($order, 'json', ['groups' => 'getOrders']);
        return new JsonResponse($jsonOrder, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/order/{id}', name: 'deleteOrder', methods: ['DELETE'])]
    public function deleteUsers(Order $order, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($order);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/order', name:"createOrders", methods: ['POST'])] 
    public function createOrder(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,UrlGeneratorInterface $urlGenerator,UsersRepository $usersRepository,ValidatorInterface $validator): JsonResponse {
       $orders = $serializer->deserialize($request->getContent(), Order::class, 'json');
       $content = $request->toArray();
       $idUser = $content['users_id'] ?? -1;
       $orders->setUsers($usersRepository->find($idUser));
       $errors = $validator->validate($orders);

       if ($errors->count() > 0) {
           return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
       }
       $em->persist($orders);
       $em->flush();
       $jsonBook = $serializer->serialize($orders, 'json', ['groups' => 'getOrders']);
       $location = $urlGenerator->generate('order_id', ['id' => $orders->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
       return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);	
    }

    #[Route('/api/order/{id}', name:"updateOrder", methods:['PUT'])]
    public function updateOrder(Request $request, SerializerInterface $serializer, Order $order, EntityManagerInterface $em, UsersRepository $usersRepository): JsonResponse 
    {
        $updatedOrder = $serializer->deserialize($request->getContent(), 
                Order::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $order]);
        $content = $request->toArray();
        $idUser = $content['users_id'] ?? -1;
        $updatedOrder->setUsers($usersRepository->find($idUser));
        
        $em->persist($updatedOrder);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }
}
