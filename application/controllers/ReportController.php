<?php

class ReportController extends Zend_Controller_Action
{
    private $identity;
    private $exportModel;

    public function init()
    {
        $this->identity = Zend_Auth::getInstance()->getIdentity();
    }

    //отчет по месяцам
    public function indexAction()
    {
        if (!isset($this->identity)){
            $this->view->message = "Авторизуйтесь на сайте, используя ваши логин и пароль";
            return;
        } else {
            $this->view->loggedin = true;
        }
        $request = $this->getRequest();
        if(!$request->isPost()) {
            $form = new Form_ReportForm();
            $this->view->form = $form;
        } else {
            $form = new Form_ReportForm();
            if ($form->isValid($request->getPost())) {
                $product_code = $form->getValue('product_code');
                $date = $form->getValue('date');
                $dateOut = $date;
            }
            $identity = Zend_Auth::getInstance()->getIdentity();
            $model = new Model_DataByArea();
            $result = $model->fetchFromDb($date, $product_code);
            $arr = $result->toArray();
            $forWho = $arr[0]['for_who'];
            unset($arr);
            // сохраняем данные для экспорта
            $session = new Zend_Session_Namespace('identity');
            $session->identity = $identity;
            $session->date = $date;
            $session->product_code = $product_code;

            // проверяем, не пустые ли данные
            if (count($result->toArray()) == 0){
                $this->view->message = "Код продукции: $product_code <br>Дата: $date <br> Нет данных, свяжитесь с администрацией<br>";
                return;
            }

            $outputLines = $model->prepareForOutput($result);

            $sumAll = array_shift($outputLines);
            $this->view->out = $outputLines;
            $this->view->sumAll = $sumAll;
            $this->view->forWho = $forWho;
            $this->view->n = 70;
            $this->view->date = $dateOut;
        }

    }

    //отчет только по коду продукции, без фильтра по месяцу
    public function productreportAction()
    {
        if (!isset($this->identity)){
            $this->view->message = "Авторизуйтесь на сайте, используя ваши логин и пароль";
            return;
        } else {
            $this->view->loggedin = true;
        }
        $request = $this->getRequest();
        if(!$request->isPost()) {
            $form = new Form_ProductReportForm();
            $this->view->form = $form;
        } else {
            $form = new Form_ProductReportForm();
            if ($form->isValid($request->getPost())) {
                $product_code = $form->getValue('product_code');
                $date = $form->getValue('date');
                $dateOut = $date;
            }
            $identity = Zend_Auth::getInstance()->getIdentity();
            $model = new Model_DataByDay();
            $result = $model->fetchFromDb($product_code);
            $arr = $result->toArray();
            $forWho = $arr[0]['for_who'];
            unset($arr);
            // сохраняем данные для экспорта
            $session = new Zend_Session_Namespace('identity');
            $session->identity = $identity;
            $session->date = $date;
            $session->product_code = $product_code;

            // проверяем, не пустые ли данные
            if (count($result->toArray()) == 0){
                $this->view->message = "Код продукции: $product_code <br>Дата: $date <br> Нет данных, свяжитесь с администрацией<br>";
                return;
            }

            $outputLines = $model->prepareForOutputByDate($result);

            $sumAll = array_shift($outputLines);
            $this->view->out = $outputLines;
            $this->view->sumAll = $sumAll;
            $this->view->forWho = $forWho;
            $this->view->n = 70;
            $this->view->date = $dateOut;
        }

    }

    public function exportbydayAction()
    {
        $model = new Model_DataByDay;

        $session = new Zend_Session_Namespace('identity');
        $data = $model->fetchFromDb($session->product_code);
        $this->_export($model, $data);
    }

    public function exportbyareaAction()
    {
        $model = new Model_DataByArea;

        $session = new Zend_Session_Namespace('identity');
        $data = $model->fetchFromDb($session->date, $session->product_code);
        $this->_export($model, $data);
    }

    private function _export($model, $data)
    {
        include '../library/PHPExcel.php';
        $data = $model->forExport($data);
        $finalData = array();
        $sumAll = array_shift($data);
        // обработка строк - делаем так $formattedData[номер листа][номер столбца][строка]
        $n = 30; // количество строк в столбце
        $pointer = 0;
        $listCount = (int)(count($data)/($n*4));
        for ($list=0; $list <= $listCount; $list++){
            for ($col=0; $col<4; $col++){
                for ($j=0; $j<$n; $j++){
                    $formattedData[$list][$col][$j] = $data[$pointer];
                    $pointer++;
                    if (!isset($data[$pointer]))
                        break;
                }
                if (!isset($data[$pointer]))
                break;
            }
        }


        for ($list=0; $list <= $listCount;){
            for ($j=0; $j<$n;){
                $finalData[] = array(
                    // PHPExcel сам конвертит в нужную кодировку
                    $formattedData[$list][0][$j],
                    $formattedData[$list][1][$j],
                    $formattedData[$list][2][$j],
                    $formattedData[$list][3][$j]
                );
                $j++;
            }
            $finalData[] = array( '----', '----', '----', '----' );
            $list++;
        }

// ставим язык
        $locale = 'ru';
        $validLocale = PHPExcel_Settings::setLocale($locale);
        if (!$validLocale) {
            echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
        }


        $objPHPExcel = new PHPExcel(); // создать файл с 1 листом
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'всего распространено '.$sumAll); // установить значение клетки
        $objPHPExcel->getActiveSheet()->fromArray($finalData, NULL, A2);

        for ($i = 0; $i <= 4; $i++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension(chr(65+$i))->setAutoSize(true);
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="report.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        return;
    }
}

?>