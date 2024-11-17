<?php

namespace Rhys\ReviewBundle\Form;

use Rhys\ReviewBundle\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('content', TextareaType::class, [
            'label' => 'Your Review',
            'attr' => [
                'rows' => 5,
                'placeholder' => 'Write your detailed review here (minimum 50 characters)',
                'class' => 'form-control'
            ]
        ])
        ->add('rating', IntegerType::class, [
            'label' => 'Your Rating (1-10)',
            'attr' => [
                    'min' => 1,
                    'max' => 10,
                    'class' => 'form-control'
                ]
            ])
            ->add('contains_spoilers', CheckboxType::class, [
                'label' => 'This review contains spoilers',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
