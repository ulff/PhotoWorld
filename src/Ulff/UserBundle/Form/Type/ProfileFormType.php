<?php

namespace Ulff\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class ProfileFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('firstname', null, array('label' => 'form.firstname', 'translation_domain' => 'FOSUserBundle'));
        $builder->add('lastname', null, array('label' => 'form.lastname', 'translation_domain' => 'FOSUserBundle'));
    }

    public function getName()
    {
        return 'ulff_user_profile';
    }

}
