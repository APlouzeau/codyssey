<?php

namespace App\Form;

use App\Entity\Enonce;
use App\Entity\Tips;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('content', TextType::class)
            ->add('expected_results', TextType::class)
            ->add('xp_gain', IntegerType::class)
            ->add('life_number', IntegerType::class)
            ->add('timer', TimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('tips', EntityType::class, [
                'class' => Tips::class,
                'choice_label' => 'name',
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
