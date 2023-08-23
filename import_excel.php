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
      $excelReader = PHPExcel_IOFactory::createReaderForFile($targetPath);
      $excelObj = $excelReader->load($targetPath);
      $worksheet = $excelObj->getSheet(0);
      $max_row = $worksheet->getHighestRow();

      if (
         $worksheet->getCell('A'.'1')->getValue() != "customer_code" || 
         $worksheet->getCell('B'.'1')->getValue() != "customer_description" || 
         $worksheet->getCell('C'.'1')->getValue() != "customer_address" || 
         $worksheet->getCell('D'.'1')->getValue() != "customer_city" || 
         $worksheet->getCell('E'.'1')->getValue() != "customer_state" || 
         $worksheet->getCell('F'.'1')->getValue() != "pl_number" || 
         $worksheet->getCell('G'.'1')->getValue() != "DD" || 
         $worksheet->getCell('H'.'1')->getValue() != "PO" || 
         $worksheet->getCell('I'.'1')->getValue() != "item_code" || 
         $worksheet->getCell('J'.'1')->getValue() != "group" || 
         $worksheet->getCell('K'.'1')->getValue() != "item_description" ||
         $worksheet->getCell('L'.'1')->getValue() != "color_description" ||  
         $worksheet->getCell('M'.'1')->getValue() != "cartoon_no" ||
         $worksheet->getCell('N'.'1')->getValue() != "size" ||
         $worksheet->getCell('O'.'1')->getValue() != "EAN code" ||
         $worksheet->getCell('P'.'1')->getValue() != "quantity"){
      
            echo(0);
         
         }
      else{
         for ($i = 2; $i <= $max_row; $i++) {
            $customer_code = $worksheet->getCell('A'.$i)->getValue(); 
            $customer_description = $worksheet->getCell('B'.$i)->getValue();
            $customer_address = $worksheet->getCell('C'.$i)->getValue();
            $customer_city = $worksheet->getCell('D'.$i)->getValue(); 
            $customer_state = $worksheet->getCell('E'.$i)->getValue(); 
            $pl_number = str_replace(' ', '',$worksheet->getCell('F'.$i)->getValue());
            $dd = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($worksheet->getCell('G'.$i)->getValue())); 
            $po = $worksheet->getCell('H'.$i)->getValue();
            $item_code = $worksheet->getCell('I'.$i)->getValue();
            $group = $worksheet->getCell('J'.$i)->getValue();
            $item_description = $worksheet->getCell('K'.$i)->getValue();
            $color_description = $worksheet->getCell('L'.$i)->getValue();
            $cartoon_no = $worksheet->getCell('M'.$i)->getValue();
            $size = $worksheet->getCell('N'.$i)->getValue();
            $ean_code = $worksheet->getCell('O'.$i)->getValue();
            $quantity = $worksheet->getCell('P'.$i)->getValue();

            $sql = "INSERT INTO temp_packing_list_dari_italy
                      (xid, customer_code, customer_description, customer_address, customer_city, customer_state,
                       pl_number, dd, po, item_code, `group`, item_description, color_description, cartoon_no, size,
                       ean_code, quantity, nopo, access, komp, userby)
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
                        '".trim($innopo)."',
                        now(),
                        '".$_SESSION[$domainApp."_mygroup"]." # ".$_SESSION[$domainApp."_mylevel"]."', 
                        '".$_SESSION[$domainApp."_myname"]."'
                     )";

            if ($customer_code != "" && $customer_description != "" && $customer_address != "" && $customer_city != "" &&
               $customer_state != "" && $pl_number != "" && $dd != "" && $po != "" && $item_code != "" && $group != "" &&
               $item_description != "" && $color_description != "" && $cartoon_no != "" && $size != "" && 
               $ean_code != "" && $quantity != "") {
               
               mysql_query($sql,$conn);
            
            }
         }
         echo("Upload Sukses");
      }
   }
   else{
      echo("Upload Gagal!");
   }
   unlink($targetPath);
}
?>