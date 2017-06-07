<?php

class Model_Preparedata
{
    public function fetchFromDb($date, $product_code, $byProductCode = false){
        $table = new Zend_Db_Table('data');
        $table->getAdapter()->query("SET character_set_results='utf8'");
        $table->getAdapter()->query("SET NAMES 'utf8'");
        $select = $table->select()->where('product_code = ?', $product_code);

        if(!$byProductCode)
        {
            $select->where('date = ?', $date);
        }


        $result = $table->fetchAll($select);
        return $result;
    }

    public function fetchByProductCode($product_code)
    {
        $table = new Zend_Db_Table('data');
        $table->getAdapter()->query("SET character_set_results='utf8'");
        $table->getAdapter()->query("SET NAMES 'utf8'");
        $select = $table->select()->where('product_code = ?', $product_code);
        $result = $table->fetchAll($select);
        return $result;
    }

    public function countData($result){
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
        $sumAll = 0;
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

        ksort($byAreaDateStreet);
        foreach ($byAreaDateStreet as $key => $value){
            ksort($value);
            $byAreaDateStreet[$key] = $value;
            foreach ($value as $key2=>$value2){
                ksort($value2);
                $byAreaDateStreet[$key][$key2] = $value2;
                foreach ($value2 as $key3=>$value3){
                    ksort($value3);
                    $byAreaDateStreet[$key][$key2][$key3] = $value3;
                }
            }
        }
        // считаем суммы по районам
        foreach ($sumAreaDay as $key => $value) {
            $sumAreaDay[$key]['sum'] = array_sum($value);
            if (strlen((string)$sumAreaDay[$key]['sum'])>3){
                $sumAreaDay[$key]['sum'] = (string)$sumAreaDay[$key]['sum'];
                $sumAreaDay[$key]['sum'] = substr_replace($sumAreaDay[$key]['sum'], ' ', -3, 0);
            }
            foreach ($value as $k => $day){
                $x = 0;
                if (strlen((string)$day>3)){
                    $sumAreaDay[$key][$k] = (string)$day;
                    $sumAreaDay[$key][$k] = substr_replace($sumAreaDay[$key][$k], ' ', -3, 0);
                }
            }
        }
        ksort($sumAreaDay);
        $return['sumAreaDay'] = $sumAreaDay;
        $return['byAreaDateStreet'] = $byAreaDateStreet;
        $return['sumAll'] = $sumAll;
        return $return;
    }

    public function prepareForOutput($result){
        $data = $this->countData($result);
        $sumAreaDay = $data['sumAreaDay'];
        $byAreaDateStreet = $data['byAreaDateStreet'];
        $sumAll = $data['sumAll'];
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
                        $outputLines[count($outputLines)-1] .= " -- " . $item['quantity'];
                        if(!empty($item['fail'])){
                            $outputLines[count($outputLines)-1] .= " -- " . $item['fail'];
                        }
                   endforeach;
               endforeach;
           endforeach;
        endforeach;
        array_unshift($outputLines, $sumAll);
        return $outputLines;
    }

    public function forExport($result){
        $data = $this->countData($result);
        $sumAreaDay = $data['sumAreaDay'];
        $byAreaDateStreet = $data['byAreaDateStreet'];
        $sumAll = $data['sumAll'];

        //$outputLines
        foreach ($sumAreaDay as $key => $value):
           $outputLines[] = $key . ' - всего '. $value['sum']; // Вывод райноа и суммы по району
           foreach ($byAreaDateStreet[$key] as $date => $street):
              $outputLines[] = $date . ' - всего '. $sumAreaDay[$key][$date]; // вывод даты внутри района, суммы по дате
               foreach ($street as $str => $item):
                   $outputLines[] = $str; // вывод улицы
                   foreach ($item as $item): // вывод номер дома+корпус, если есть корпус
                       $outputLines[] = 'Дом '.$item['house'];
                        if(!empty($item['korpus'])){
                            $outputLines[count($outputLines)-1] .= "/ к." . $item['korpus'];
                        }
                        $outputLines[count($outputLines)-1] .= " - " . $item['quantity'];
                   endforeach;
               endforeach;
           endforeach;
        endforeach;
        array_unshift($outputLines, $sumAll);
        return $outputLines;
    }

}