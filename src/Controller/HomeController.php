<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;

class HomeController extends AbstractController
{
    private $productRepository;
    private $categoryRepository;
    private $entityManager;

    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        ManagerRegistry $doctrine)
        {
            $this->productRepository = $productRepository;
            $this->categoryRepository = $categoryRepository;
            $this->entityManager = $doctrine->getManager();
        }
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        $categories = $this->categoryRepository->findAll();
        return $this->render('home/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'photo_url' => 'http://127.0.0.1:8000/uploads/'
        ]);
    }

    #[Route('/product/{category}', name: 'product_category')]
    public function categoryProduct(category $category):Response
    {
        $categories = $this->categoryRepository->findAll();
        return $this->render('home/index.html.twig',[
            'products' => $category->getProducts(),
            'categories' => $categories,
            'photo_url' => 'http://127.0.0.1:8000/uploads/'
        ]);
    }
}
