<?php
namespace AppBundle\Form\Type\Tariff;

use AppBundle\Entity\TariffType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class TariffType1 extends AbstractType {
    
    public static $numChoices;
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('type', 'choice', array(
                    'choices' => TariffType::getChoices()
                ))
                ->add('price', 'integer', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Range(array('min' => 10, 'max' => 100))
                    )
                ))
                ->add('requestPrice', 'checkbox', array(
                    'required' => false
                ))
                ->add('numDiscount', 'checkbox', array(
                    'required' => false
                ))
                ->add('minNum', 'choice', array(                    
                    'choices' => TariffType1::$numChoices,
                    'attr' => array(
                        'class' => 'num-picker'
                    ),
                    'constraints' => array(
                        new NotBlank(array('groups' => 'num-discount'))
                    )
                ))
                ->add('priceDiscount', 'integer', array(
                    'required' => false,
                    'constraints' => array(
                        new NotBlank(array('groups' => 'num-discount')),
                        new Range(array('min' => 10, 'max' => 100))
                    )
                ));
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'validation_groups' => function(FormInterface $form) {
                $data = $form->getData();
                $grps = array('Default');
                if ($data['numDiscount'])
                    array_push ($grps, 'num-discount');
                return $grps;
            }
        ));
    }
    
    public function getName() {
        return "form";
    }
    
    public static function init() {
        TariffType1::$numChoices = array();
        for ($i = 3; $i < 10; $i++)
            TariffType1::$numChoices[$i] = "{$i} STD.";
    }
}

TariffType1::init();
