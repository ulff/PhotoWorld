<?php

namespace Ulff\PhotoWorldBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of AlbumType
 *
 * @author ulff
 */
class AlbumType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('title');
        $builder->add('description', 'textarea', array('required' => false));
    }

    public function getName() {
        return 'createalbum';
    }

}
