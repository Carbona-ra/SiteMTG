<?php

namespace App\Form;

use App\Entity\Deck;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeckSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'Deck Name'
            ])
            ->add('commanderName', TextType::class, [
                'required' => false,
                'label' => 'Commander Name'
            ])
            ->add('Creator', EntityType::class, [
                'required' => false,
                'class' => User::class,
                'choice_label' => 'name',
            ])
        ;
    }

}
