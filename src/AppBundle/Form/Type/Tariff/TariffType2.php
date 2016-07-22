<?php
namespace AppBundle\Form\Type\Tariff;

use AppBundle\Entity\TariffType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class TariffType2 extends AbstractType {
    
    public static $numChoices;
    public static $minChoices;
    
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
                ->add('minNum', 'choice', array(
                    'choices' => TariffType2::$minChoices,
                    'attr' => array(
                        'class' => 'num-picker'
                    )
                ))
                ->add('discount', 'checkbox', array(
                    'required' => false
                ))
                ->add('discountMinNum', 'choice', array(                    
                    'choices' => TariffType2::$numChoices,
                    'attr' => array(
                        'class' => 'num-picker'
                    ),
                    'constraints' => array(
                        new NotBlank(array('groups' => 'num-discount')),
                        new Callback(array(
                            'callback' => array($this, 'validate'),
                            'groups' => 'num-discount'
                        ))
                    )
                ))
                ->add('discountPrice', 'integer', array(
                    'required' => false,
                    'constraints' => array(
                        new NotBlank(array('groups' => 'num-discount')),
                        new Range(array('min' => 10, 'max' => 100))
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
    
    public function validate($value, ExecutionContextInterface $context) {
        $data = $context->getRoot()->getData();
        if (intval($data['minNum']) >= intval($data['discountMinNum']))
            $context->addViolation('Dieser Wert muss größer als Mindestanzahl Personen sein');
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'validation_groups' => function(FormInterface $form) {
                $data = $form->getData();
                $grps = array('Default');
                if ($data['discount'])
                    array_push ($grps, 'num-discount');
                if ($data['ownPlace'])
                    array_push($grps, 'own-place');
                return $grps;
            }
        ));
    }
    
    public function getName() {
        return "form";
    }
    
    public static function init() {
        TariffType2::$minChoices = array();
        for ($i = 2; $i < 10; $i++)
            TariffType2::$minChoices[$i] = "{$i} PER.";
        TariffType2::$numChoices = array();
        for ($i = 3; $i < 10; $i++)
            TariffType2::$numChoices[$i] = "{$i} PER.";
    }
}

TariffType2::init();
