<?php
// src/AppBundle/Form/RegistrationType.php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('name');
        $builder->add('surname');
        $builder->add('email');
        $builder->add('plainPassword','password');
        $builder->remove('username');
        $builder->add('accept', 'checkbox', array(                
                'required' => true,
));
                
    }

    /*/public function getParent()
    {
        return 'fos_user_registration';
    }
    */
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        //$this->setUsername($this->getName() . $this->GetSurname());
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
        ));
    }
    
    public function getName()
    {
        return 'app_user_registration';
    }
}