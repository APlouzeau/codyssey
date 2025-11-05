<?php

namespace App\Form;

use App\Entity\Enonce;
use App\Entity\Tips;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content')
            ->add('expected_results')
            ->add('xp_gain')
            ->add('life_number')
            ->add('timer', null, [
                'widget' => 'single_text',
            ])
            ->add('tips', EntityType::class, [
                'class' => Tips::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Enonce::class,
        ]);
    }
}
