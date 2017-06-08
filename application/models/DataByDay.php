<?php

class Model_DataByDay
{

    public function fetchFromDb($product_code)
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

        // группируем по датам
        $sumAll = 0;
        $byDate = array();
        foreach ($data as $i => $item) {
            $byDate[$item['task_date']][] = $item;
            $sumAll += $item['quantity'];
        }

        uksort($byDate, array($this, 'sortByDateFunctionPHP52'));
        $byDate = array_reverse($byDate);

        // группируем по районам
        $byDateArea = array();
        foreach ($byDate as $date => $items) {
            foreach ($items as $item) {
                $byDateArea[$date][$item['area']][] = $item;
            }
        }

        // группируем по улицам
        $byDateAreaStreet = array();
        foreach ($byDateArea as $date => $areas) {
            foreach ($areas as $area => $items) {
                foreach ($items as $item){
                    $byDateAreaStreet[$date][$area][$item['street']][] = $item;
                    //$sumAreaDay[$areakey][$datekey] += $item['quantity']; // сумма в день по району
                }
            }
        }


        $return = array();
        $return['byDateAreaStreet'] = $byDateAreaStreet;
        $return['sumAll'] = $sumAll;
        return $return;

        // группируем по улицам
        $sumAreaDay = array();
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
        return $return;
    }

    public function prepareForOutputByDate($result){
        $data = $this->countData($result);
        $byDateAreaStreet = $data['byDateAreaStreet'];

        $sumByDate = $this->getSumByDate($byDateAreaStreet);
        $sumByDateArea = $this->getSumByDateArea($byDateAreaStreet);

        $sumAll = $data['sumAll'];
        //$outputLines
        foreach ($sumByDate as $date => $sum):
            $outputLines[] = '<h2>' .$date . ' - всего '. $sum . '</h2>'; // Вывод даты и суммы по дате
            foreach ($byDateAreaStreet[$date] as $area => $streets):
                $outputLines[] = '<h3>' .$area . ' - всего '. $sumByDateArea[$date][$area] . '</h3>'; // вывод даты внутри района, суммы по дате
                foreach ($streets as $street => $items):
                    $outputLines[] = $street; // вывод улицы
                    foreach ($items as $item): // вывод номер дома+корпус, если есть корпус
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

    public function getSumByDate($data)
    {
        $sumByDate = array();
        foreach ($data as $date => $areas) {
            $sumByDate[$date] = 0;
            foreach ($areas as $area) {
                foreach ($area as $street => $items) {
                    foreach ($items as $item) {
                        $sumByDate[$date] += $item['quantity'];
                    }
                }
            }
        }

        return $sumByDate;
    }

    public function getSumByDateArea($data)
    {
        $sumByDateArea = array();
        foreach ($data as $date => $areas) {
            $sumByDateArea[$date] = array();
            foreach ($areas as $area => $streets) {
                $sumByDateArea[$date][$area] = 0;
                foreach ($streets as $street => $items) {
                    foreach ($items as $item) {
                        $sumByDateArea[$date][$area] += $item['quantity'];
                    }
                }
            }
        }

        return $sumByDateArea;
    }
    
    public function sortByDateFunction( $a, $b ) {
        return
            DateTime::createFromFormat('j.n.Y', $a)->getTimestamp()
            - DateTime::createFromFormat('j.n.Y', $b)->getTimestamp();
    }

    public function sortByDateFunctionPHP52( $a, $b ) {

        $aDate = explode('.', $a);
        $bDate = explode('.', $b);
        if($aDate[2] !== $bDate[2])
        {
            return $aDate[2] - $bDate[2];
        }
        if($aDate[1] !== $bDate[1])
        {
            return $aDate[1] - $bDate[1];
        }
        return $aDate[0] - $bDate[0];

    }

    public function forExport($result){

        $data = $this->countData($result);
        $byDateAreaStreet = $data['byDateAreaStreet'];

        $sumByDate = $this->getSumByDate($byDateAreaStreet);
        $sumByDateArea = $this->getSumByDateArea($byDateAreaStreet);

        $sumAll = $data['sumAll'];

        //$outputLines
        foreach ($sumByDate as $date => $sum):
           $outputLines[] = $date . ' - всего '. $sum; // Вывод даты и суммы по дате
           foreach ($byDateAreaStreet[$date] as $area => $streets):
              $outputLines[] = $area . ' - всего '. $sumByDateArea[$date][$area]; // вывод суммы по району в определенный день
               foreach ($streets as $str => $items):
                   $outputLines[] = $str; // вывод улицы
                   foreach ($items as $item): // вывод номер дома+корпус, если есть корпус
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