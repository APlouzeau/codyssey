<?php

namespace App\Form;

use App\Entity\Avatar;
use App\Entity\Enonce;
use App\Entity\Language;
use App\Entity\Level;
use App\Entity\LevelType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LevelFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number')
            ->add('score')
            ->add('avatar', EntityType::class, [
                'class' => Avatar::class,
                'choice_label' => 'id',
            ])
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'id',
            ])
            ->add('enonce', EntityType::class, [
                'class' => Enonce::class,
                'choice_label' => 'id',
            ])
            ->add('type', EntityType::class, [
                'class' => self::class,
                'choice_label' => 'id',
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
