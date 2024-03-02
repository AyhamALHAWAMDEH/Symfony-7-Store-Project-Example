<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;

#[Route('/product/crud')]
class ProductCrudController extends AbstractController
{

    private SluggerInterface $slugger;
    private LoggerInterface $logger;

    public function __construct(SluggerInterface $slugger, LoggerInterface $logger)
    {
        $this->slugger = $slugger;
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_product_crud_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product_crud/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_crud_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $file */
            $file = $form->get('productImage')->getData(); 
    
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
    
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'), 
                        $newFilename
                    );
                } catch (FileException $e) {
                    
                    $this->logger->error(sprintf('An error occurred while uploading the file: %s', $e->getMessage()));
    
                    $this->addFlash('error', 'There was a problem uploading your product image.');
                }
    
                $product->setProductImage($newFilename); 
            }
    
            $entityManager->persist($product);
            $entityManager->flush();
    
            $this->addFlash('success', 'Product created successfully.');
    
            return $this->redirectToRoute('app_product_crud_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('product_crud/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'app_product_crud_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product_crud/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_crud_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $form = $this->createForm(ProductType::class, $product);
    $form->handleRequest($request);
    $originalImage = $product->getProductImage();

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile|null $file */
        $file = $form->get('productImage')->getData(); // Ensure 'productImage' matches your form field name

        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
                // Optionally, delete the old image if necessary
                if ($originalImage && file_exists($this->getParameter('uploads_directory').'/'.$originalImage)) {
                    unlink($this->getParameter('uploads_directory').'/'.$originalImage);
                }
                $product->setProductImage($newFilename);
            } catch (FileException $e) {
                // Log error or handle as appropriate
                $this->logger->error(sprintf('An error occurred while uploading the file: %s', $e->getMessage()));

                // Optionally, re-set the original image if upload fails
                $product->setProductImage($originalImage);
            }
        } else {
            // If no new file was uploaded, keep the original file
            $product->setProductImage($originalImage);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Product updated successfully.');

        return $this->redirectToRoute('app_product_crud_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('product_crud/edit.html.twig', [
        'product' => $product,
        'form' => $form->createView(),
    ]);
}


    #[Route('/{id}', name: 'app_product_crud_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_crud_index', [], Response::HTTP_SEE_OTHER);
    }
}
