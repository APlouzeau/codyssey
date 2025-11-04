<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangeEmailFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'NOUVEL E-MAIL',
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner un e-mail'),
                ],
                'attr' => [
                    'autocomplete' => 'email',
                ],
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => 'MOT DE PASSE ACTUEL',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner votre mot de passe'),
                ],
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
        ]);
    }
}