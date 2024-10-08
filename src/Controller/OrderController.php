<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Product;
use App\Entity\Order;


class OrderController extends AbstractController
{
    private $orderRepository;
    private $entityManager;

    public function __construct(
        OrderRepository $orderRepository,
        ManagerRegistry $doctrine)
        {
            $this->orderRepository = $orderRepository;
            $this->entityManager = $doctrine->getManager();
        }
   
    #[Route('/orders', name: 'orders_list')]
    public function index(): Response
    {
        $orders =$this->orderRepository->findBy([
            'Etat' => 'ordered'
        ]);
        return $this->render('order/index.html.twig', [
            'orders' => $orders,]);
    }

    #[Route('/user/orders', name: 'user_order_list')]
    public function userOrders(): Response
    {    // Check if user is logged in
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // Get EntityManager
        $entityManager = $this->entityManager;

        // Get cart items for the current user
        $cartItems = $entityManager->getRepository(Order::class)
            ->findBy([
                'user' => $this->getUser(),
                'Etat' => 'cart']);
               

        return $this->render('order/user.html.twig', [
            'Orders' => $cartItems,
        ]);
    }

    #[Route('/profile/AfficherOrdres/', name: 'app_ordres_affichage')]
    public function afficheOrders():Response
    {
        $user=$this->getUser();
        return $this->render('order/user.html.twig', ['user'=>$user]);
    }

    #[Route('/store/order/{id}', name: 'order_store')]
    public function store(ProductRepository $ProductRepository, $id): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
         $product=$ProductRepository->find($id);

        $order = new Order();
        $order->setPname($product->getName());
        $order->setPrice($product->getPrice());
        $order->setStatus('processing...');
        $order->setUser($this->getUser());
            $this->entityManager->persist($order);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'Order Saved!'
            );
         return $this->redirectToRoute('app_ordres_affichage');
    }

    #[Route('/update/order/{order}/{status}', name: 'order_status_update')]
    public function updateOrderStatus(Order $order, $status): Response
    {
        $order->setStatus($status);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'your order status was updated'
        );
    
     return $this->redirectToRoute('orders_list');
    }

    #[Route('/update/order/{order}', name: 'order_delete')]
    public function deleteOrder(Order $order): Response
    {
        $this->entityManager->remove($order);
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'your order was removed'
        );
    
     return $this->redirectToRoute('orders_list');
    }
    #[Route('/cart/checkout', name: 'cart_checkout')]
    public function checkout(): Response
    {
        // Check if user is logged in
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // Get EntityManager
        $entityManager = $this->entityManager;

        // Get cart items for the current user
        $cartItems = $entityManager->getRepository(Order::class)
            ->findBy([
                'user' => $this->getUser(),
                'Etat' => 'cart'
            ]);

        if (empty($cartItems)) {
            $this->addFlash('warning', 'Your cart is empty.');
            return $this->redirectToRoute('home');
        }

        // Change the status of cart items to 'ordered' when checking out
        foreach ($cartItems as $cartItem) {
            $cartItem->setEtat('ordered');
            $cartItem->setStatus('Processing...');
        }

        $entityManager->flush();

        $this->addFlash(
            'success',
            'Checkout successful!'
        );

        return $this->redirectToRoute('user_order_list');
    }
}

