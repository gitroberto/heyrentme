<?php
namespace AppBundle\Form\Type\Tariff;

use AppBundle\Entity\TariffType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class TariffType4 extends AbstractType {
    
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
                ->add('duration', 'integer', array(
                    'constraints' => array(
                        new NotBlank()
                    )
                ))
                ->add('requestPrice', 'checkbox', array(
                    'required' => false
                ));
    }

    public function getName() {
        return "form";
    }    
}
