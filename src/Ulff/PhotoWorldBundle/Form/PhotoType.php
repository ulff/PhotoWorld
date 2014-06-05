<?php

namespace Ulff\PhotoWorldBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of PhotoType
 *
 * @author ulff
 */
class PhotoType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('title');
        $builder->add('description', 'textarea', array('required' => false));
        $builder->add('photofile', 'file');
    }

    public function getName() {
        return 'photoupload';
    }

}
