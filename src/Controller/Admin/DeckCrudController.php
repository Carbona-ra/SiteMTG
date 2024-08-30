<?php

namespace App\Controller\Admin;

use App\Entity\Deck;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DeckCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Deck::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            TextField::new('commanderName', 'Nom du commander'),
            AssociationField::new('Creator', 'Createur')
                ->autocomplete(),
        ];
    }
    
}
