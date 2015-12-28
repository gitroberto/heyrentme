<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EquipmentType extends AbstractType {
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', 'text')
            ->add('save', 'submit')
        ;
    }

    public function getName() {
        return 'app_equipment';
    }
}
