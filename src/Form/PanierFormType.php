<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PanierFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'label' => "Nom"
            ])
            ->add('prenom', TextType::class, [
                'required' => false,
                'label' => "Prénom"
            ])
            ->add('adresse', TextType::class, [
                'required' => false,
                'label' => "Adresse"
            ])
            ->add('codePostal', TextType::class, [
                'required' => false,
                'label' => "Code Postal"
            ])
            ->add('ville', TextType::class, [
                'required' => false,
                'label' => "Ville"
            ])
            ->add('pays', TextType::class, [
                'required' => false,
                'label' => "Pays"
            ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class
        ]);
    }
}
