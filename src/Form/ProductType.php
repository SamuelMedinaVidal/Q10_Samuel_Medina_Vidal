<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre del producto',
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'Ej: Manzanas Gala'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'El nombre es obligatorio'])
                ]
            ])
            ->add('precio', MoneyType::class, [
                'label' => 'Precio (€)',
                'currency' => 'EUR',
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => '0.00'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'El precio es obligatorio']),
                    new Positive(['message' => 'El precio debe ser mayor que cero'])
                ]
            ])
            ->add('cantidad', IntegerType::class, [
                'label' => 'Stock disponible',
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'Cantidad en stock',
                    'min' => 0
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La cantidad es obligatoria']),
                    new PositiveOrZero(['message' => 'La cantidad no puede ser negativa'])
                ]
            ])
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'Describe tu producto...',
                    'rows' => 4
                ]
            ])
            ->add('categoria', ChoiceType::class, [
                'label' => 'Categoría',
                'attr' => [
                    'class' => 'form-control form-control-custom'
                ],
                'choices' => [
                    'Frutas y Verduras' => 'frutas-verduras',
                    'Carnes y Embutidos' => 'carnes-embutidos',
                    'Lácteos y Huevos' => 'lacteos-huevos',
                    'Panadería y Bollería' => 'panaderia-bolleria',
                    'Conservas y Enlatados' => 'conservas-enlatados'
                ],
                'placeholder' => 'Selecciona una categoría',
                'constraints' => [
                    new NotBlank(['message' => 'Selecciona una categoría'])
                ]
            ])
            ->add('ubicacion', TextType::class, [
                'label' => 'Ubicación / Tienda',
                'required' => false,
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'placeholder' => 'Ej: Barcelona, Calle Mayor 123'
                ]
            ])
            ->add('imagenFile', FileType::class, [
                'label' => 'Imagen del producto',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control form-control-custom',
                    'accept' => 'image/jpeg,image/png,image/webp'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Por favor, sube una imagen válida (JPEG, PNG o WebP)',
                        'maxSizeMessage' => 'El archivo es demasiado grande. Máximo: 5MB'
                    ])
                ]
            ])
            ->add('activo', CheckboxType::class, [
                'label' => 'Producto activo (visible en tienda)',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
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
