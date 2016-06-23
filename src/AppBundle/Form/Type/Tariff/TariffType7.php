<?php
namespace AppBundle\Form\Type\Tariff;

use AppBundle\Entity\TariffType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class TariffType7 extends AbstractType {

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
                ->add('discount', 'checkbox', array(
                    'required' => false
                ))
                ->add('discountMinNum', 'choice', array(                    
                    'choices' => TariffType7::$numChoices,
                    'attr' => array(
                        'class' => 'num-picker'
                    ),
                    'constraints' => array(
                        new NotBlank(array('groups' => 'num-discount'))
                    )
                ))
                ->add('discountPrice', 'integer', array(
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
                if ($data['discount'])
                    array_push ($grps, 'num-discount');
                return $grps;
            }
        ));
    }

    public function getName() {
        return "form";
    }    
    
    public static function init() {
        TariffType7::$numChoices = array();
        for ($i = 3; $i < 10; $i++)
            TariffType7::$numChoices[$i] = "{$i} T";
    }
}

TariffType7::init();