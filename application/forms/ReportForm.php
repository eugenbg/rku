<?php

class Form_ReportForm extends Zend_Form
{
    public function __construct($options = null) {
        parent::__construct($options);

        $productCodes = $this->getProductCodes();

        $productCode = new Zend_Form_Element_Select('product_code', array(
            "label" => "Код продукции",
            "required" => true,
        ));

        //вывести имеющиеся коды и даты, для удобства
/*        foreach ($productCodes as $row) {
            if($row['product_code'] == 0)
                continue;

            $productCode->addMultiOptions(
                array($row['product_code'] => $row['product_code'])
            );
        }

        $months = $this->getMonths();
        $date = new Zend_Form_Element_Select('date', array(
            "label" => "Месяц + год, например: Сентябрь 2013",
            "required" => true,
        ));

        foreach ($months as $row) {
            $date->addMultiOptions(
                array($row['date'] => $row['date'])
            );
        }*/

        $productCode = new Zend_Form_Element_Text('product_code');
        $productCode   ->setLabel('Код продукции')
            ->setRequired(true);

        $date = new Zend_Form_Element_Text('date');
        $date   ->setLabel('Месяц + год, например: Сентябрь 2013')
            ->setRequired(true);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Смотреть отчет');


        $this->addElement($productCode);
        $this->addElement($date);
        $this->addElement($submit);

        $this->setMethod('post');
        $this->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().('report'));
    }


    public function getMonths()
    {
        $table = new Zend_Db_Table('data');
        $table->getAdapter()->query("SET character_set_results='utf8'");
        $table->getAdapter()->query("SET NAMES 'utf8'");
        $select = $table
            ->select()
            ->from('data','date')
            ->order('task_date')
            ->distinct();

        $result = $table->fetchAll($select);
        return $result;
    }

    public function getProductCodes()
    {
        $table = new Zend_Db_Table('data');
        $table->getAdapter()->query("SET character_set_results='utf8'");
        $table->getAdapter()->query("SET NAMES 'utf8'");
        $select = $table
            ->select()
            ->from('data',array('product_code', 'client'))
            ->order('product_code')
            ->distinct();

        $result = $table->fetchAll($select);
        return $result;
    }

}

