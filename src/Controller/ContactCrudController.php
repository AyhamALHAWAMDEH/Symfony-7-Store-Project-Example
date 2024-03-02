<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contact/crud')]
class ContactCrudController extends AbstractController
{
    #[Route('/', name: 'app_contact_crud_index', methods: ['GET'])]
    public function index(ContactRepository $contactRepository): Response
    {
        return $this->render('contact_crud/index.html.twig', [
            'contacts' => $contactRepository->findAll(),
        ]);
    }



    #[Route('/{id}', name: 'app_contact_crud_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render('contact_crud/show.html.twig', [
            'contact' => $contact,
        ]);
    }


    #[Route('/{id}', name: 'app_contact_crud_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contact);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contact_crud_index', [], Response::HTTP_SEE_OTHER);
    }
}
