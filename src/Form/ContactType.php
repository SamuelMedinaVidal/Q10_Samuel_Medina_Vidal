<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('FirstName', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'Tu nombre'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, introduce tu nombre'])
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre de empresa',
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'Nombre de tu empresa'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, introduce el nombre de tu empresa'])
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Apellidos',
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'Tus apellidos'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, introduce tus apellidos'])
                ]
            ])
            ->add('mail', EmailType::class, [
                'label' => 'Correo electrónico',
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'tu@email.com'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, introduce tu email'])
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Mensaje',
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'Cuéntanos sobre tu empresa y productos...',
                    'rows' => 5
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, introduce un mensaje'])
                ]
            ])
            ->add('img', FileType::class, [
                'label' => 'Logo o imagen (JPEG/PNG, máx. 5MB)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'accept' => 'image/jpeg,image/png'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Por favor, sube una imagen válida (JPEG o PNG)',
                        'maxSizeMessage' => 'El archivo es demasiado grande. Máximo permitido: 5MB'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
