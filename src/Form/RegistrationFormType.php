<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex; // Ajoutez cette ligne

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class)
            ->add('email', EmailType::class)
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 12, // Changement ici : minimum 12 caractères
                        'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                    new Regex([ // Contrainte de complexité
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])/',
                        // Explication de la Regex :
                        // ^                  # Début de la chaîne
                        // (?=.*[a-z])        # Doit contenir au moins une minuscule
                        // (?=.*[A-Z])        # Doit contenir au moins une majuscule
                        // (?=.*\d)           # Doit contenir au moins un chiffre
                        // (?=.*[^\w\s])      # Doit contenir au moins un caractère spécial (tout ce qui n'est pas lettre, chiffre ou espace)
                        // Note : on n'a pas besoin de l'ancre de fin ($) car la contrainte Length gère la longueur.
                        'message' => 'Votre mot de passe doit contenir au moins 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.',
                    ]),
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
