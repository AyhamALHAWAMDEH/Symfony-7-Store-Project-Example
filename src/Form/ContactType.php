<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom ou société'])
            ->add('email', EmailType::class, ['label' => 'Adresse e-mail'])
            ->add('subject', TextType::class, ['label' => 'Sujet'])
            ->add('message', TextareaType::class, ['label' => 'Message'])
        ;
    }
}
