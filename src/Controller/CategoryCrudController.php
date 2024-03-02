<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;

#[Route('/category/crud')]
class CategoryCrudController extends AbstractController
{

    private SluggerInterface $slugger;
    private LoggerInterface $logger;

    public function __construct(SluggerInterface $slugger, LoggerInterface $logger)
    {
        $this->slugger = $slugger;
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_category_crud_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category_crud/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_category_crud_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('categoryImage')->getData();

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                }  catch (FileException $e) {
                    $this->logger->error(sprintf('An error occurred while uploading the file: %s', $e->getMessage()));
                }

                $category->setCategoryImage($newFilename);
            }

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Category created successfully.');

            return $this->redirectToRoute('app_category_crud_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category_crud/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_category_crud_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category_crud/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_crud_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        $originalImage = $category->getCategoryImage();
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('categoryImage')->getData();
    
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
    
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    // Delete the old image if necessary
                    if ($originalImage && file_exists($this->getParameter('uploads_directory').'/'.$originalImage)) {
                        unlink($this->getParameter('uploads_directory').'/'.$originalImage);
                    }
                    $category->setCategoryImage($newFilename);
                } catch (FileException $e) {
                    $this->logger->error(sprintf('An error occurred while uploading the file: %s', $e->getMessage()));
                    // Optionally, re-set the original image if upload fails
                    $category->setCategoryImage($originalImage);
                }
            } else {
                // If no new file was uploaded, keep the original file
                $category->setCategoryImage($originalImage);
            }
    
            $entityManager->flush();
            $this->addFlash('success', 'Category updated successfully.');
    
            return $this->redirectToRoute('app_category_crud_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('category_crud/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/{id}', name: 'app_category_crud_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_category_crud_index', [], Response::HTTP_SEE_OTHER);
    }
}
