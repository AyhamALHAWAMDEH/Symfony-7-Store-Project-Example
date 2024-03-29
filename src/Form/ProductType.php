<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Offer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('category', EntityType::class, [
            'required' => true,
            'label' => false,
            'class' => Category::class,
            'choice_label' => 'categoryName',
        ])
        ->add('productName', TextType::class, [
            'required' => true,
            'label' => false,
        ])
        ->add('productPrice', NumberType::class, [
            'required' => true,
            'label' => false,
        ])
        
        ->add('shortDescription', TextareaType::class, [
            'required' => true,
            'label' => false,
        ])
        ->add('longDescription', TextareaType::class, [
            'required' => true,
            'label' => false,
        ])
        
        ->add('productBrand', TextType::class, [
            'required' => true,
            'label' => false,
        ])
        ->add('productOrigin', TextType::class, [
            'required' => true,
            'label' => false,
        ])
        ->add('productImage', FileType::class, [
            'data_class' => null,
            'required' => true,
            'label' => false,
            'multiple' => false,
            'attr' => [
                'accept' => 'image/*',
            ],
            'help' => 'The image must be a file of type: jpg, jpeg, png, gif.',
            'constraints' => [
                new File([
                    'maxSize' => '2048k',
                    'mimeTypes' => [
                        'image/jpg',
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image.',
                ])
            ],
        ])
        ->add('offer', EntityType::class, [
            'required' => false, 
            'label' => false,
            'class' => Offer::class,
            'choice_label' => 'type',
            'placeholder' => 'Select an offer or leave empty', 
        ])        
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
