<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]

    public function index(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Sending the email
            $email = (new Email())
                ->from($data['email'])
                ->to('ayham.test.2024@gmail.com')
                ->subject($data['subject'])
                ->text($data['message']);

            $mailer->send($email);

            // Storing the message in the database
            $contact = new Contact();
            $contact->setName($data['name']);
            $contact->setEmail($data['email']);
            $contact->setSubject($data['subject']);
            $contact->setMessage($data['message']);

            $entityManager->persist($contact);
            $entityManager->flush();

            // Adding a success message
            $this->addFlash('success', 'Your message has been sent successfully!');

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

