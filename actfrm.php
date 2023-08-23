<?php
  include("../../configuration.php");
  include("../../connection.php");
  include("../../endec.php");

  if(isset($_POST['intxtmode'])){
    $intxtmode = $_POST['intxtmode'];
  }

if($intxtmode=='checktemp') {
  $sql = "SELECT xid FROM temp_packing_list_dari_italy 
          WHERE userby = '".$_SESSION[$domainApp."_myname"]."'";  
  $result =  mysql_query($sql,$conn);
  $row =  mysql_num_rows($result);

  if ($row > 0) {
    echo 1;
  }
  else{
    echo 0;
  }
  mysql_free_result($result);
}
else if($intxtmode=='transfer') {
  $sql = "SELECT * FROM temp_packing_list_dari_italy 
          WHERE userby = '".$_SESSION[$domainApp."_myname"]."'";  
  $result =  mysql_query($sql,$conn);

  $sukses = 0;
  $gagal = 0;

  while($data = mysql_fetch_array($result)){
    $xid = $data["xid"];
    $customer_code = $data["customer_code"]; 
    $customer_description = $data["customer_description"];
    $customer_address = $data["customer_address"];
    $customer_city = $data["customer_city"]; 
    $customer_state = $data["customer_state"]; 
    $pl_number = $data["pl_number"]; 
    $dd = $data["dd"]; 
    $po = $data["po"];
    $item_code = $data["item_code"];
    $group = $data["group"];
    $item_description = $data["item_description"];
    $color_description = $data["color_description"];
    $cartoon_no = $data["cartoon_no"];
    $size = $data["size"];
    $ean_code = $data["ean_code"];
    $quantity = $data["quantity"];
    $nopo = $data["nopo"];
    // $status = 0;

    $sql_1 = "SELECT SUM(plqtykrm) as plqtykrm from tbl_rucoline_packing_list a 
              WHERE a.plnopl like '".$pl_number."' AND a.plnopo = '".$po."' AND a.plnokarton = '".$cartoon_no."'";
    $result_1 =  mysql_query($sql_1,$conn);
    $row_1 =  mysql_num_rows($result_1);

    // echo $sql_1;

    if ($row_1 > 0) {
      while ($data_1 = mysql_fetch_array($result_1)) {

        $plqtykrm = $data_1["plqtykrm"];

        if (is_null($plqtykrm)) {
          $status = 1;
        }
        else if ($plqtykrm <= 0) {
          // mengahpus packing list dengan no PO
          $sql_2 = "DELETE FROM tbl_rucoline_packing_list 
                    WHERE plnopl like '".$pl_number."' and plnopo = '".$po."' and plnokarton = '".$cartoon_no."'";
          mysql_query($sql_2,$conn);

          $sql_3 = "UPDATE rpinspek
                    SET 
                    inpack = 0,
                    innopl = '',
                    innokarton = ''
                    WHERE innopo = '".$po."' AND innopl = '".$pl_number."' AND innokarton = '".$cartoon_no."'";
          mysql_query($sql_3,$conn);
          $status = 1;
        }
        else if ($plqtykrm > 0) {
          $status = 0;
        }
      }
    }
    else {
      $status = 1;
    }

    // echo "status : ".$status." \n plqtykrm".$plqtykrm;

    // data tidak ada masalah
    if ($status == 1) {
      // mengambil kode warna sesuai no PO
      $sql_4 = "SELECT warna FROM tbl_rucoline_po
                WHERE nopo = '".$po."' AND eancode = '".$ean_code."' ";
      $result_4 =  mysql_query($sql_4,$conn);
      $row_4 =  mysql_num_rows($result_4);

      // echo $sql_4;

      if ($row_4 > 0) {
        // memasukkan data no packing list dan no karton di tabel inspection

        $limit = 0;

        while ($data_4 = mysql_fetch_array($result_4)) {
          $kdwarna = $data_4["warna"];

          $sql_5 = "SELECT * FROM rpinspek 
                    WHERE incust = 'RL' AND innopo = '".$po."' AND ininner = '".$ean_code."' AND inqc = 1 AND inpack = 0 ";
          $result_5 = mysql_query($sql_5,$conn);
          $row_5 = mysql_num_rows($result_5);

          // echo $sql_5;

          if ($row_5 > 0) {
            while ($data_5 = mysql_fetch_array($result_5)) {
              $noqc = $data_5["innobukti"];
              $nobaris = $data_5["inbaris"];
              $nomp = $data_5["innomp"];
              $noso = $data_5["innoso"];
              $limit = $limit + 1;

              if ($limit <= $quantity){
                $sql_6 = "UPDATE rpinspek 
                          SET
                          inpack = 0,
                          innopl = '".$pl_number."',
                          innokarton = '".$cartoon_no."'
                          WHERE 
                          innopo = '".$po."' AND 
                          ininner = '".$ean_code."' AND 
                          innobukti = '".$noqc."' AND 
                          innomp = '".$nomp."' AND 
                          innoso = '".$noso."' AND
                          inbaris = '".$nobaris."'";
                if (!mysql_query($sql_6,$conn)){
                  die('Error: ' . mysql_error());
                }
                
                // echo($sql_6);
              }

              $sql_7 = "INSERT INTO tbl_rucoline_packing_list 
                        (plkdcust, plnmcust, plalmcust, plkotacust, plnegcust, plnopl, pltglkrm, plnopo, plartikel, plgroup,
                        plmaterial, plkdwarna, plwarna, plnokarton, plsize, pleancode, plqty, plnomp, plnoso, access, komp, userby) 
                        VALUES (
                          '".$customer_code."',
                          '".$customer_description."',
                          '".$customer_address."',
                          '".$customer_city."',
                          '".$customer_state."',
                          '".$pl_number."',
                          '".$dd."',
                          '".$po."',
                          '".$item_code."',
                          '".$group."',
                          '".$item_description."',
                          '".$kdwarna."',
                          '".$color_description."',
                          '".$cartoon_no."',
                          '".$size."',
                          '".$ean_code."',
                          '".$quantity."',
                          '".$nomp."',
                          '".$noso."',
                          now(),
                          '".$_SESSION[$domainApp."_mygroup"]." # ".$_SESSION[$domainApp."_mylevel"]."', 
                          '".$_SESSION[$domainApp."_myname"]."'
                        )";
              
              // echo($sql_7);

              if (!mysql_query($sql_7,$conn)){
                die('Error: ' . mysql_error());
              }
            }
          }
          else{
            echo("Data Kode Warna Tidak Ada ... !");
            $gagal++;
          }
          $sukses++;
        }
      }
    }
  }

  // hapus data rekap packing list yang sudah terbuat
  $sql_8 = "DELETE FROM tbl_rucoline_rekap_packing_list WHERE pcnopo = '".$nopo."'";
  
  if (!mysql_query($sql_8,$conn)){
    die('Error: ' . mysql_error());
  }

  // ambil data packing list yang sudah terbuat
  $sql_9 = "SELECT * FROM tbl_rucoline_packing_list WHERE plnopo = '".$nopo."' ORDER BY plnmcust";
  // echo($sql_9);
  $result_9 = mysql_query($sql_9,$conn);

  while ($data_9 = mysql_fetch_array($result_9)) {
    $kdcust = trim($data_9["plkdcust"]);
    $nmcust = trim($data_9["plnmcust"]);
    $alamat = trim($data_9["plalmcust"]);
    $kota = trim($data_9["plkotacust"]);
    $negara = trim($data_9["plnegcust"]);
    $nopl = trim($data_9["plnopl"]);
    $nokarton = trim($data_9["plnokarton"]);
    $artikel = trim($data_9["plartikel"]);
    $material = trim($data_9["plmaterial"]);
    $kdwarna = trim($data_9["plkdwarna"]);
    $nmwarna = trim($data_9["plwarna"]);
    $size = trim($data_9["plsize"]);
    $eancode = trim($data_9["pleancode"]);
    $qty = trim($data_9["plqty"]);
    
    $group = trim($data_9["plartikel"]);
    $group = explode("-", $group);
    $group = $group[0];

    $rekap = $data_9["plsize"];
    $check_9 = substr($rekap, -1);

    if ($check_9 == 0) {
      $param_9 = "pc".substr($rekap, 0, 2);
    }
    else{
      $param_9 = "pc".substr($rekap, 0, 2)."s";
    }

    // cek di tabel rekap packing list

    $sql_10 = "SELECT * FROM tbl_rucoline_rekap_packing_list
               WHERE 
               pcnmcust = '".$nmcust."' AND 
               pcnopo = '".$nopo."' AND 
               pcartikel = '".$artikel."' AND 
               pcmaterial = '".$material."' AND 
               pckdwarna = '".$kdwarna."'
               ";
    $result_10 = mysql_query($sql_10,$conn);
    $row_10 = mysql_num_rows($result_10);

    // echo $sql_10;

    if ($row_10 > 0) {
      // update tabel rekap packing list
      while ($data_10 = mysql_fetch_array($result_10)) {
        $pctotal = $data_10["pctotal"];
        $sql_11 = "UPDATE tbl_rucoline_rekap_packing_list
                   SET
                   ".$param_9." = '".($rekap + $qty)."',
                   pctotal = '".($pctotal + $qty)."'
                   WHERE
                   pcnmcust = '".$nmcust."' AND 
                   pcnopo = '".$nopo."' AND 
                   pcartikel = '".$artikel."' AND 
                   pcmaterial = '".$material."' AND 
                   pckdwarna = '".$kdwarna."'
                  ";
        
        // echo($sql_11);

        if (!mysql_query($sql_11,$conn)){
          die('Error: ' . mysql_error());
        }
      }
    }
    else{
      // masuk di tabel rekap packing list
      $sql_12 = "INSERT INTO tbl_rucoline_rekap_packing_list
                (pckdcust, pcnmcust, pcalmcust, pckotacust, pcnegcust, pcnopo, pcartikel, pcgroup, 
                pcmaterial, pckdwarna, pcwarna, ".$param_9." ,pctotal, access, komp, userby)
                VALUES
                (
                  '".$kdcust."',
                  '".$nmcust."',
                  '".$alamat."',
                  '".$kota."',
                  '".$negara."',
                  '".$nopo."',
                  '".$artikel."',
                  '".$group."',
                  '".$material."',
                  '".$kdwarna."',
                  '".$nmwarna."',
                  '".$qty."',
                  '".$qty."',
                  now(),
                  '".$_SESSION[$domainApp."_mygroup"]." # ".$_SESSION[$domainApp."_mylevel"]."', 
                  '".$_SESSION[$domainApp."_myname"]."'
                )
                ";
      
      // echo($sql_12);
      
      if (!mysql_query($sql_12,$conn)){
        die('Error: ' . mysql_error());
      }
    }

  }

  $sql_13 = "TRUNCATE temp_packing_list_dari_italy";
  if (!mysql_query($sql_13,$conn)){
    die('Error: ' . mysql_error());
  }

  echo(($sukses + $gagal)."|".$sukses."|".$gagal);
  mysql_free_result($result);
}

mysql_close($conn);
?>