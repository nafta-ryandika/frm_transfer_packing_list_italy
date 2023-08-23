<?php
include("../../configuration.php");
include("../../connection.php");

require_once '../../PHPExcel/PHPExcel/IOFactory.php';

$fileName = $_FILES['file']['name'];
$fileSize = $_FILES['file']['size'];
$fileError = $_FILES['file']['error'];

if(isset($_POST['innopo'])){
   $innopo = $_POST['innopo'];
}


if($fileSize > 0 || $fileError == 0){
   $targetPath = 'temp/'.$fileName;
   $move = move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);

   chmod($targetPath,0777);

   if ($move) {
      $excel = PHPExcel_IOFactory::load($targetPath);

      foreach($excel->getWorksheetIterator() as $data){
         $max_row = $data->getHighestRow();

         if (
            $data->getCellByColumnAndRow(0,1)->getValue() != "customer_code" || 
            $data->getCellByColumnAndRow(1,1)->getValue() != "customer_description" || 
            $data->getCellByColumnAndRow(2,1)->getValue() != "customer_address" || 
            $data->getCellByColumnAndRow(3,1)->getValue() != "customer_city" || 
            $data->getCellByColumnAndRow(4,1)->getValue() != "customer_state" || 
            $data->getCellByColumnAndRow(5,1)->getValue() != "pl_number" || 
            $data->getCellByColumnAndRow(6,1)->getValue() != "DD" || 
            $data->getCellByColumnAndRow(7,1)->getValue() != "PO" || 
            $data->getCellByColumnAndRow(8,1)->getValue() != "item_code" || 
            $data->getCellByColumnAndRow(9,1)->getValue() != "group" || 
            $data->getCellByColumnAndRow(10,1)->getValue() != "item_description" ||
            $data->getCellByColumnAndRow(11,1)->getValue() != "color_description" ||  
            $data->getCellByColumnAndRow(12,1)->getValue() != "cartoon_no" ||
            $data->getCellByColumnAndRow(13,1)->getValue() != "size" ||
            $data->getCellByColumnAndRow(14,1)->getValue() != "EAN code" ||
            $data->getCellByColumnAndRow(15,1)->getValue() != "quantity"){
            
            echo(0);
         }
         else{
            for($i = 2; $i <= $max_row; $i++){ 
               $customer_code = $data->getCellByColumnAndRow(0,$i)->getValue(); 
               $customer_description = $data->getCellByColumnAndRow(1,$i)->getValue();
               $customer_address = $data->getCellByColumnAndRow(1,$i)->getValue();
               $customer_city = $data->getCellByColumnAndRow(3,$i)->getValue(); 
               $customer_state = $data->getCellByColumnAndRow(4,$i)->getValue(); 
               $pl_number = $data->getCellByColumnAndRow(5,$i)->getValue(); 
               $dd = $data->getCellByColumnAndRow(6,$i)->getValue(); 
               $po = $data->getCellByColumnAndRow(7,$i)->getValue();
               $item_code = $data->getCellByColumnAndRow(8,$i)->getValue();
               $group = $data->getCellByColumnAndRow(9,$i)->getValue();
               $item_description = $data->getCellByColumnAndRow(10,$i)->getValue();
               $color_description = $data->getCellByColumnAndRow(11,$i)->getValue();
               $cartoon_no = $data->getCellByColumnAndRow(12,$i)->getValue();
               $size = $data->getCellByColumnAndRow(13,$i)->getValue();
               $ean_code = $data->getCellByColumnAndRow(14,$i)->getValue();
               $quantity = $data->getCellByColumnAndRow(15,$i)->getValue();

               $sql = "INSERT INTO temp_packing_list_dari_italy
                      (xid, customer_code, customer_description, customer_address, customer_city, customer_state,
                       pl_number, dd, po, item_code, `group`, item_description, color_description, cartoon_no, size,
                       ean_code, quantity, nopo)
                      VALUES
                      (
                        '',
                        '".trim($customer_code)."',
                        '".trim($customer_description)."',
                        '".trim($customer_address)."',
                        '".trim($customer_city)."',
                        '".trim($customer_state)."',
                        '".trim($pl_number)."',
                        '".trim($dd)."',
                        '".trim($po)."',
                        '".trim($item_code)."',
                        '".trim($group)."',
                        '".trim($item_description)."',
                        '".trim($color_description)."',
                        '".trim($cartoon_no)."',
                        '".trim($size)."',
                        '".trim($ean_code)."',
                        '".trim($quantity)."',
                        '".trim($innopo)."'
                     )";

                     // mysql_query($sql,$conn);
               if ($customer_code != "" && $customer_description != "" && $customer_address != "" && $customer_city != "" &&
                  $customer_state != "" && $pl_number != "" && $dd != "" && $po != "" && $item_code != "" && $group != "" &&
                  $item_description != "" && $color_description != "" && $cartoon_no != "" && $size != "" && 
                  $ean_code != "" && $quantity != "") {
                  mysql_query($sql,$conn);
               }    
               // flush();
            }
            // echo("Upload Sukses");
            echo $sql;
         }
      }
   }
   else{
      echo("Upload Gagal!");
   }
   unlink($targetPath);
}
?>