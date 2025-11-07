<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\UserSkin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChangeUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupération des skins débloqués depuis les options
        $unlockedSkinsJs = $options['unlocked_skins_js'] ?? [];
        $unlockedSkinsPhp = $options['unlocked_skins_php'] ?? [];
        $unlockedSkinsPy = $options['unlocked_skins_py'] ?? [];

        $builder
            ->add('email', EmailType::class, [
                'label' => 'NOUVEL E-MAIL',
                'required' => false,
                'attr' => [
                    'autocomplete' => 'email',
                ],
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => 'MOT DE PASSE ACTUEL',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner votre mot de passe actuel'),
                ],
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'first_options' => [
                    'label' => 'NOUVEAU MOT DE PASSE',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'CONFIRMATION DU MOT DE PASSE',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
            ])
            ->add('avatar_js', ChoiceType::class, [
                'label' => 'AVATAR JAVASCRIPT',
                'mapped' => false,
                'required' => false,
                'expanded' => true,
                'multiple' => false,
                'choices' => $unlockedSkinsJs,
            ])
            ->add('avatar_php', ChoiceType::class, [
                'label' => 'AVATAR PHP',
                'mapped' => false,
                'required' => false,
                'expanded' => true,
                'multiple' => false,
                'choices' => $unlockedSkinsPhp,
            ])
            ->add('avatar_py', ChoiceType::class, [
                'label' => 'AVATAR PYTHON',
                'mapped' => false,
                'required' => false,
                'expanded' => true,
                'multiple' => false,
                'choices' => $unlockedSkinsPy,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_token_id' => 'change_user_form',
            'unlocked_skins_js' => [],
            'unlocked_skins_php' => [],
            'unlocked_skins_py' => [],
        ]);
    }
}
