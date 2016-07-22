<?php
namespace AppBundle\Form\Type\Tariff;

use AppBundle\Entity\TariffType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Test\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class TariffType5 extends AbstractType {
    
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
                ->add('ownPlace', 'checkbox', array(
                    'required' => false
                ))
                ->add('addrStreet', 'text', array(
                    'constraints' => array(
                        new NotBlank(array('groups' => 'own-place')),
                        new Length(array('max' => 128))
                    )
                ))
                ->add('addrNumber', 'text', array(
                    'constraints' => array(
                        new NotBlank(array('groups' => 'own-place')),
                        new Length(array('max' => 16))
                    )
                ))
                ->add('addrFlatNumber', 'text', array(
                    'required' => false,
                    'constraints' => array(
                        new Length(array('max' => 16))
                    )
                ))
                ->add('addrPostcode', 'text', array(
                    'constraints' => array(
                        new NotBlank(array('groups' => 'own-place')),
                        new Length(array('max' => 4)),
                        new Regex(array('pattern' => '/^\d{4}$/', 'message' => 'Bitte gib hier eine gültige PLZ ein'))
                    )
                ))
                ->add('addrPlace', 'text', array(
                    'constraints' => array(
                        new NotBlank(array('groups' => 'own-place')),
                        new Length(array('max' => 128))
                    )
                ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'validation_groups' => function(FormInterface $form) {
                $data = $form->getData();
                $grps = array('Default');
                if ($data['ownPlace'])
                    array_push($grps, 'own-place');
                return $grps;
            }
        ));
    }
    
    public function getName() {
        return "form";
    }    
}
