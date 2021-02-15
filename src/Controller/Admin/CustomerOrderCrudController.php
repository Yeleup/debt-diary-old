<?php

namespace App\Controller\Admin;

use App\Entity\CustomerOrder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomerOrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CustomerOrder::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->setPermission(Action::DELETE, "ROLE_ADMIN")
            ->setPermission(Action::NEW, "ROLE_ADMIN");
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setDefaultSort(['updated' => "ASC"]);
        return $crud; // TODO: Change the autogenerated stub
    }

    public function configureFields(string $pageName): iterable
    {
        $confirmed = BooleanField::new('confirmed')->onlyOnIndex();

        if ($this->isGranted("ROLE_ADMIN")) {
            return [
                TextField::new('amount'),
                AssociationField::new('payment'),
                AssociationField::new('type'),
                AssociationField::new('customer'),
                AssociationField::new('user'),
                $confirmed,
                DateField::new('updated')->setFormat('y-MM-dd HH:mm:ss'),
            ];
        } else {

            $confirmed->formatValue(function ($value, $entity) {

                if ($entity->getPayment() && $this->getUser()->getPayments()) {
                    foreach ($this->getUser()->getPayments() as $payment) {
                        if ($entity->getPayment()) {
                            if ($payment->getId() == $entity->getPayment()->getId()) {
                                return $value;
                            }
                        }
                    }
                }

                return '';
            });

            return [
                TextField::new('amount'),
                TextField::new('payment'),
                TextField::new('type'),
                TextField::new('customer'),
                TextField::new('user'),
                $confirmed,
                DateField::new('updated')->setFormat('y-MM-dd HH:mm:ss')->onlyOnIndex(),
            ];
        }
    }

}
