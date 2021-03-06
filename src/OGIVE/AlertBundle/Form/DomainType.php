<?php

namespace OGIVE\AlertBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class DomainType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('name', null, array('required' => false))
                ->add('description', null, array('required' => false))
                ->add('subDomains', CollectionType::class, array(
                    'entry_type' => SubDomainType::class,
                    'allow_add' => true,
                    'by_reference' => false,
                    'allow_delete' => true
                ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'OGIVE\AlertBundle\Entity\Domain'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'ogive_alertbundle_domain';
    }

}
