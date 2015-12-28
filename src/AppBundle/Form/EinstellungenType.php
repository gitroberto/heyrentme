<?php
namespace AppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;

class EinstellungenType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('password','password', array( 'required'=>false ));
        $builder->add('newPassword','password', array( 'required'=>false, 'constraints' => array(
                    new Callback(array($this, 'validateNewPassword'))
                ) ));
        $builder->add('repeatedPassword','password', array( 'required'=>false ));
        
        $builder->add('name');
        $builder->add('surname');
        
        $builder->add('phone', 'integer', array( 'required'=>false ));
        $builder->add('phonePrefix', 'integer' , array( 'required'=>false ));
        
        $builder->add('iban', 'text', array( 'required'=>false ));
        $builder->add('bic', 'text' ,array( 'required'=>false ));
    }
    
    public function validateNewPassword($data, ExecutionContextInterface $context){
        if ($data['newPassword'] != null){
            
        }
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        //$this->setUsername($this->getName() . $this->GetSurname());
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
            
        ));
    }
    
    public function getName()
    {
        return 'form_einstellungen';
    }
}
