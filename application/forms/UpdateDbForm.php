<?php

class Form_UpdateDbForm extends Zend_Form
{

    public function __construct($options = null) {
        parent::__construct($options);

        $this->setName('updateDb');

        $file = new Zend_Form_Element_File('file');
        $file   ->setLabel('table csv file')
                ->setRequired(true);

        $upload = new Zend_Form_Element_Submit('load');
        $upload->setLabel('Load');

        $this->addElement($file);
        $this->addElement($upload);

        $this->setMethod('post');
        $this->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().(''));
        }


}