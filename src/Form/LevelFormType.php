<?php

namespace App\Form;

use App\Entity\Avatar;
use App\Entity\Enonce;
use App\Entity\Language;
use App\Entity\Level;
use App\Entity\LevelType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LevelFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', IntegerType::class)
            ->add('score', IntegerType::class)
            ->add('avatar', EntityType::class, [
                'class' => Avatar::class,
                'choice_label' => 'name',
            ])
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'name',
            ])
            ->add('enonce', EntityType::class, [
                'class' => Enonce::class,
                'choice_label' => 'title',
            ])
            ->add('type', EntityType::class, [
                'class' => LevelType::class,
                'label' => 'Type de niveau',
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Level::class,
        ]);
    }
}
