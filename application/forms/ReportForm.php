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

        foreach ($productCodes as $row) {
            if($row['product_code'] == 0)
                continue;

            $productCode->addMultiOptions(
                array($row['product_code'] => $row['product_code'])
            );
        }

        $months = $this->getMonths();
        $dateSelect = new Zend_Form_Element_Select('date', array(
            "label" => "Месяц + год, например: Сентябрь 2013",
            "required" => true,
        ));

        foreach ($months as $row) {
            $dateSelect->addMultiOptions(
                array($row['date'] => $row['date'])
            );
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Смотреть отчет');


        $this->addElement($productCode);
        $this->addElement($dateSelect);
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
            ->from('data','product_code')
            ->order('product_code')
            ->distinct();

        $result = $table->fetchAll($select);
        return $result;
    }

}

