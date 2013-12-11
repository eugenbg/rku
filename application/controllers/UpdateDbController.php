<?php

class UpdateDbController extends Zend_Controller_Action
{

    private $uploadPath;
    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        if($this->_request->isPost()){
            $this->view->message .= $this->clearUploadFolder().'<br>';
            //usleep(1000000);
            $file = var_dump($this->unzipUploadedFile());
            $path = $this->uploadPath.$file[2];
            $model = new Model_UpdateDb();
            $row = $model->update($path);
            $this->view->result = "В базу данных загружено $row записей";
        } else {
            $form = new Form_UpdateDbForm();
            $this->view->form = $form;
        }
    }

    public function clearUploadFolder() {

     // init the debug string
     $debugStr = '';
     $debugStr .= "Deleting Contents Of: $path<br /><br />";
     $path = $this->uploadPath = APPLICATION_PATH . '/files/';
     // parse the folder
     IF ($handle = OPENDIR($path)) {

          WHILE (FALSE !== ($file = READDIR($handle))) {

               IF ($file != "." && $file != "..") {

               // If it's a file, delete it
               IF(IS_FILE($path."/".$file)) {

                    IF(UNLINK($path."/".$file)) {
                    $debugStr .= "Deleted File: ".$file."<br />";
                    }

               } ELSE {

                    // It's a directory...
                    // crawl through the directory and delete the contents
                    IF($handle2 = OPENDIR($path."/".$file)) {

                         WHILE (FALSE !== ($file2 = READDIR($handle2))) {

                              IF ($file2 != "." && $file2 != "..") {
                                   IF(UNLINK($path."/".$file."/".$file2)) {
                                   $debugStr .= "Deleted File: $file/$file2<br />";
                                   }
                              }

                         }

                    }

                    IF(RMDIR($path."/".$file)) {
                    $debugStr .= "Directory: ".$file."<br />";
                    }

               }

               }

          }

     }
     RETURN $debugStr;
}



    public function unzipUploadedFile()
    {
        $zip = new ZipArchive;
        $res = $zip->open($_FILES['file']['tmp_name']);

        if ($res === TRUE) {
            $zip->extractTo($this->uploadPath);
            $file = scandir($this->uploadPath);
            $zip->close();
            $this->view->message .= 'Распакованный файл '.$file.' <br>';
        } else {
            $this->view->result = 'Невозможно распаковать файл';
        }
        return $file;
    }
}

