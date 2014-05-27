<?php

namespace Thibaud\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'attr' => array('placeholder' => 'Nom', 'class' => 'form-control'),
            'constraints' => array(
                new Assert\NotBlank(),
                new Assert\Length(array('min' => 4))
            )
        ))
        ->add('email', 'email', array(
            'attr' => array('placeholder' => 'Email', 'class' => 'form-control'),
            'constraints' => array(
                new Assert\NotBlank(),
                new Assert\Email()
            )
        ))
        ->add('subject', 'text', array(
            'attr' => array('placeholder' => 'Sujet', 'class' => 'form-control'),
            'constraints' => array(
                new Assert\NotBlank()
            )
        ))
        ->add('message', 'textarea', array(
            'attr' => array('placeholder' => 'Message', 'class' => 'form-control'),
            'constraints' => array(
                new Assert\NotBlank()
            )
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false
        ));
    }

    public function getName()
    {
        return 'Contact';
    }
}