<?php

class ReportController extends Zend_Controller_Action
{

    public function init()
    {

    }

    public function indexAction()
    {
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
            $table = new Zend_Db_Table('data');
            $table->getAdapter()->query("SET character_set_results='utf8'");
            $table->getAdapter()->query("SET NAMES 'utf8'");
            $select = $table->select()
                                ->where('client = ?', $identity->client) // выбираем из базы данные для залогиненного клиента
                                ->where('date = ?', $date)
                                //->limit(200,0)
                                ->where('product_code = ?', $product_code);
            $result = $table->fetchAll($select);
            // проверяем, не пустые ли данные
            if (count($result->toArray()) == 0){
                $this->view->message = "Код клиента: $identity->client <br>Дата: $date <br> Нет данных, свяжитесь с администрацией<br>";
                return;
            }

            // помещаем данные в $data, удаляем из task_date ' 0:00:00' - лишнее
            foreach ($result as $i => $item) {
                foreach ($item as $key => $value) {
                    if (strpos($value, ' 0:00:00')) {
                        $newValue = mb_substr($value, 0, strpos($value, ' 0:00:00'));
                        $data[$i][$key] = $newValue;
                    } else {
                       $data[$i][$key] = $value;
                    }
                }
            } // дальше

            // группируем по районам
            foreach ($data as $i => $item) {
                    $byArea[$data[$i]['area']][] = $data[$i];
                    $sumAll += $item['quantity'];
            }
            // группируем по датам $byArea[чижовка][$i]
            foreach ($byArea as $areaname => $area) {
                foreach ($area as $i => $item) {
                    $byAreaDate[$areaname][$item['task_date']][] = $item;
                }
            }
            // группируем по улицам
            foreach ($byAreaDate as $areakey => $area) {
                foreach ($area as $datekey => $date) {
                     foreach ($date as $item){
                         $byAreaDateStreet[$areakey][$datekey][$item['street']][] = $item;
                         $sumAreaDay[$areakey][$datekey] += $item['quantity']; // сумма в день по району
                     }
                }
            }

            // считаем суммы по районам
            foreach ($sumAreaDay as $key => $value) {
                $sumAreaDay[$key]['sum'] = array_sum($value);
            }


            //$outputLines
            foreach ($sumAreaDay as $key => $value):
               $outputLines[] = '<h2>' .$key . ' - всего '. $value['sum'] . '</h2>'; // Вывод райноа и суммы по району
               foreach ($byAreaDateStreet[$key] as $date => $street):
                  $outputLines[] = '<h3>' .$date . ' - всего '. $sumAreaDay[$key][$date] . '</h3>'; // вывод даты внутри района, суммы по дате
                   foreach ($street as $str => $item):
                       $outputLines[] = $str; // вывод улицы
                       foreach ($item as $item): // вывод номер дома+корпус, если есть корпус
                           $outputLines[] = $item['house'];
                            if(!empty($item['korpus'])){
                                $outputLines[count($outputLines)-1] .= "/" . $item['korpus'];
                            }
                            $outputLines[count($outputLines)-1] .= " - " . $item['quantity'];
                       endforeach;
                   endforeach;
               endforeach;
            endforeach;

        $this->view->out = $outputLines;
        $this->view->sumAll = $sumAll;
        $this->view->n = 30;
        $this->view->date = $dateOut;

            /* echo "<pre>";
            print_r($byAreaDateStreet);
            echo "</pre>";*/
        }



    }


}

?>