<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'Tu nombre'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, introduce tu nombre'])
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
            ->add('email', EmailType::class, [
                'label' => 'Correo electrónico',
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'tu@email.com'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, introduce tu email']),
                    new Email(['message' => 'Por favor, introduce un email válido'])
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Contraseña',
                    'attr' => [
                        'class' => 'form-control form-control-custom',
                        'placeholder' => 'Mínimo 6 caracteres',
                        'autocomplete' => 'new-password'
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmar contraseña',
                    'attr' => [
                        'class' => 'form-control form-control-custom',
                        'placeholder' => 'Repite tu contraseña',
                        'autocomplete' => 'new-password'
                    ],
                ],
                'invalid_message' => 'Las contraseñas no coinciden',
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, introduce una contraseña']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Tu contraseña debe tener al menos {{ limit }} caracteres',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Acepto los términos y condiciones',
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'constraints' => [
                    new IsTrue(['message' => 'Debes aceptar los términos y condiciones'])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
