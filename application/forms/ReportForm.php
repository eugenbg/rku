<?php

class Form_ReportForm extends Zend_Form
{
    public function __construct($options = null) {
        parent::__construct($options);

        $product_code = new Zend_Form_Element_Text('product_code');
        $product_code   ->setLabel('Код продукции')
                        ->setRequired(true);

        $date = new Zend_Form_Element_Text('date');
        $date   ->setLabel('Месяц + год, например: Сентябрь 2013')
                ->setRequired(true);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Смотреть отчет');

        $this->addElement($product_code);
        $this->addElement($date);
        $this->addElement($submit);

        $this->setMethod('post');
        $this->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().('report'));
    }

}

