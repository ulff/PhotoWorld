<?php

namespace Ulff\PhotoWorldBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of MultiUploadType
 *
 * @author ulff
 */
class MultiUploadType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('files', 'file', array('multiple' => true));
    }

    public function getName() {
        return 'multiupload';
    }

}