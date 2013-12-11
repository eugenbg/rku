<?php

class Model_UpdateDb  {

    public function init() {

    }

    public function update($path) {
            $modelUpdateDb = new Zend_Db_Table('data');
            // очистим таблицу
            $modelUpdateDb->getAdapter()->query("SET character_set_results='utf8'");
            $modelUpdateDb->getAdapter()->query("SET NAMES 'utf8'");
            $modelUpdateDb->getAdapter()->query('TRUNCATE TABLE '.$modelUpdateDb->info(Zend_Db_Table::NAME));
            $row = 1;
            $nQuery = 0;
            $startQuery = 'INSERT INTO data (client, product_code, date, task_number, task_date, postman_name, street, house, korpus, for_who, street_house, fail, quantity, area) VALUES ';
            if (($handle = fopen($path, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    $endQuery .= "('$data[0]', '$data[1]', '$data[2]', '$data[3]', '$data[4]', '$data[5]', '$data[6]', '$data[7]', '$data[8]', '$data[9]', '$data[10]', '$data[11]', '$data[12]', '$data[13]'), ";
                    $row++;
                    $nQuery++;
                    //print_r($data);
                    if ($nQuery>100) {
                        $query = $startQuery.$endQuery;
                        // get rid of excess ', '
                        $query = substr($query, 0, -2);
                        // convert to UTF-8
                        //$query = iconv('windows-1251', 'utf-8', $query);
                        //echo $query . "<br>";
                        // execute query
                        $modelUpdateDb->getAdapter()->query($query);
                        // start over
                        $endQuery = '';
                        $nQuery = 0;
                    }
                }
                //finish inserting the data
                $query = $startQuery.$endQuery;
                $query = substr($query, 0, -2);
                //$query = iconv('windows-1251', 'utf-8', $query);
                $modelUpdateDb->getAdapter()->query($query);
                fclose($handle);
            }
        $row++;
        return ($row);
    }

}