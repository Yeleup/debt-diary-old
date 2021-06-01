<?php

namespace App\Controller\Admin;

use App\Entity\Market;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MarketCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Market::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title')->setLabel('market.title'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
{
    return parent::configureCrud($crud)->setEntityPermission('ROLE_ADMIN'); // TODO: Change the autogenerated stub
}
}
