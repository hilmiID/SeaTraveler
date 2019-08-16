<?php 
    include 'koneksi.php';

    $latGPS = trim($_POST["latitude"]);
    $longGPS = trim($_POST["longitude"]);

    

    function GetDrivingDistance($lat1, $lat2, $long1, $long2)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving&key=AIzaSyCwJ2Vepe9L2Miuh7QH87SR_RItIXHlX6Q";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);
        $dist = $response_a['rows'][0]['elements'][0]['distance']['value'];
        $time = $response_a['rows'][0]['elements'][0]['duration']['text'];

        return array('distance' => $dist, 'time' => $time);
    }

    $query = mysqli_query($con, "SELECT table_pantai.id_pantai, table_pantai.nama_pantai, table_pantai.biaya_masuk, table_pantai.rating, table_location.latitude, table_location.longitude FROM `table_pantai` INNER JOIN table_location ON table_pantai.id_location = table_location.id_location");

    $insert = mysqli_query($con, "INSERT into coba(no, latitude, longitude) VALUES ('','".$latGPS."', '".$longGPS."')");

    $query_kriteria = mysqli_query($con, "SELECT * FROM `table_kriteria`");
    $kriteria = $kriteria_seq = array();
    while ($row = mysqli_fetch_array($query_kriteria)){
        $kriteria[$row['kode']] = $row;
        $kriteria_seq[] = $row;
    }


            $data_awal_penilaian = array();
            while ($row = mysqli_fetch_array($query)){
                $query_transportasi = mysqli_query($con, "SELECT COUNT(jenis_transportasi) AS transportasi FROM `table_transportasi` INNER JOIN table_relasi_transportasi ON table_relasi_transportasi.id_transportasi = table_transportasi.id_transportasi INNER JOIN table_pantai ON table_relasi_transportasi.id_pantai = table_pantai.id_pantai WHERE table_pantai.id_pantai = ".$row['id_pantai']);
                $query_fasilitas = mysqli_query($con, "SELECT COUNT(jenis_fasilitas) AS fasilitas FROM `table_fasilitas` INNER JOIN table_relasi_fasilitas ON table_relasi_fasilitas.id_fasilitas = table_fasilitas.id_fasilitas INNER JOIN table_pantai ON table_relasi_fasilitas.id_pantai = table_pantai.id_pantai WHERE table_pantai.id_pantai = ".$row['id_pantai']);
                $transportasi = mysqli_fetch_array($query_transportasi);
                $fasilitas = mysqli_fetch_array($query_fasilitas);
                $jarak = GetDrivingDistance($latGPS, $row['latitude'] ,$longGPS, $row['longitude']);
                $data = array(
                    "id_pantai"=>$row['id_pantai'],
                    "nama_pantai"=>$row['nama_pantai'],
                    "biaya_masuk"=>$row['biaya_masuk'],
                    "rating"=>$row['rating'],
                    "transportasi"=>$transportasi['transportasi'],
                    "fasilitas"=>$fasilitas['fasilitas'],
                    "jarak"=>$jarak['distance'] 
                );
                $data_awal_penilaian[] = $data;
      
        } 
            $data_ternormalisasi = getKeputusanTernormalisasi($data_awal_penilaian);

            $data_terbobot = getKeputusanTerbobot(getKeputusanTernormalisasi($data_awal_penilaian), $kriteria);
  
            $matriks_ideal_positif = getMatriksIdealPositif($data_terbobot, $kriteria_seq);
           
            $matriks_ideal_negatif = getMatriksIdealNegatif($data_terbobot, $kriteria_seq);
          
            $matriks_solusi = getJarakSolusiIdeal($data_terbobot, $matriks_ideal_positif, $matriks_ideal_negatif, $kriteria_seq);

            $Ranking = getRanking($matriks_solusi);

            $json = '{"rekomendasi_pantai": [';

            for($i=0; $i<count($matriks_solusi);$i++){
                //tanda kutip dua (") tidak diijinkan oleh string json, maka akan kita replace dengan karakter `
                //strip_tag berfungsi untuk menghilangkan tag-tag html pada string 
                    $char ='"';

                    $json .= 
                    '{
                        "id_pantai":"'.str_replace($char,'`',strip_tags($Ranking[$i]['id_pantai'])).'", 
                        "nama_pantai":"'.str_replace($char,'`',strip_tags($Ranking[$i]['nama_pantai'])).'",
                        "biaya_masuk":"'.str_replace($char,'`',strip_tags($Ranking[$i]['biaya_masuk'])).'",
                        "rating":"'.str_replace($char,'`',strip_tags($Ranking[$i]['rating'])).'",
                        "transportasi":"'.str_replace($char,'`',strip_tags($Ranking[$i]['transportasi'])).'",
                        "fasilitas":"'.str_replace($char,'`',strip_tags($Ranking[$i]['fasilitas'])).'",
                        "jarak":"'.str_replace($char,'`',strip_tags($Ranking[$i]['jarak'])).'",
                        "Dplus":"'.str_replace($char,'`',strip_tags($Ranking[$i]['D+'])).'",
                        "Dminus":"'.str_replace($char,'`',strip_tags($Ranking[$i]['D-'])).'",
                        "jarak_solusi":"'.str_replace($char,'`',strip_tags($Ranking[$i]['jarak_solusi'])).'"
                    },';
            }
            // buat menghilangkan koma diakhir array
            $json = substr($json,0,strlen($json)-1);

            $json .= ']}';

            // print json
            echo $json;
            
            mysqli_close($con);

        ?> 

<?php 
    $pembagi = getPembagi($data_awal_penilaian);
    function getPembagi($data)
    {
        $pembagi = array(
            "biaya_masuk"=>0,
            "rating"=>0,
            "transportasi"=>0,
            "fasilitas"=>0,
            "jarak"=>0
        );
        for($i=0; $i<count($data);$i++){
            $pembagi['biaya_masuk'] += ($data[$i]['biaya_masuk']*$data[$i]['biaya_masuk']);
            $pembagi['rating'] += ($data[$i]['rating']*$data[$i]['rating']);
            $pembagi['transportasi'] += ($data[$i]['transportasi']*$data[$i]['transportasi']);
            $pembagi['fasilitas'] += ($data[$i]['fasilitas']*$data[$i]['fasilitas']);
            $pembagi['jarak'] += ($data[$i]['jarak']*$data[$i]['jarak']);
        }
        $pembagi['biaya_masuk'] = sqrt($pembagi['biaya_masuk']);
        $pembagi['rating'] = sqrt($pembagi['rating']);
        $pembagi['transportasi'] = sqrt($pembagi['transportasi']);
        $pembagi['fasilitas'] = sqrt($pembagi['fasilitas']);
        $pembagi['jarak'] = sqrt($pembagi['jarak']);

        return $pembagi;
    }

    function getKeputusanTernormalisasi($dataAwal)
    {
        $pembagi = getPembagi($dataAwal);
        $data_keputusan_ternormalisai = $dataAwal;
        for($i=0; $i<count($dataAwal);$i++){
            $data_keputusan_ternormalisai[$i]['biaya_masuk'] = $dataAwal[$i]['biaya_masuk']/$pembagi['biaya_masuk'];
            $data_keputusan_ternormalisai[$i]['rating'] = $dataAwal[$i]['rating']/$pembagi['rating'];
            $data_keputusan_ternormalisai[$i]['transportasi'] = $dataAwal[$i]['transportasi']/$pembagi['transportasi'];
            $data_keputusan_ternormalisai[$i]['fasilitas'] = $dataAwal[$i]['fasilitas']/$pembagi['fasilitas'];
            $data_keputusan_ternormalisai[$i]['jarak'] = $dataAwal[$i]['jarak']/$pembagi['jarak'];
        }
        return $data_keputusan_ternormalisai;
    }

    function getKeputusanTerbobot($dataTernormarlisasi, $kriteria)
    {
        $data_normalisasi_terbobot = $dataTernormarlisasi;
        for($i=0; $i<count($dataTernormarlisasi);$i++){
            $data_normalisasi_terbobot[$i]['biaya_masuk'] = $dataTernormarlisasi[$i]['biaya_masuk']*$kriteria['biaya_masuk']['bobot'];
            $data_normalisasi_terbobot[$i]['rating'] = $dataTernormarlisasi[$i]['rating']*$kriteria['rating']['bobot'];
            $data_normalisasi_terbobot[$i]['transportasi'] = $dataTernormarlisasi[$i]['transportasi']*$kriteria['transportasi']['bobot'];
            $data_normalisasi_terbobot[$i]['fasilitas'] = $dataTernormarlisasi[$i]['fasilitas']*$kriteria['fasilitas']['bobot'];
            $data_normalisasi_terbobot[$i]['jarak'] = $dataTernormarlisasi[$i]['jarak']*$kriteria['jarak']['bobot'];
        }
        return $data_normalisasi_terbobot;
    }

    function getMatriksIdealPositif($dataTerbobot, $kriteria_seq)
    {
        $matriks_ideal_positif = array('biaya_masuk'=>0, 'rating'=>0, 'transportasi'=>0, 'fasilitas'=>0, 'jarak'=>0);
        $matriks_ideal = array('biaya_masuk'=>array(), 'rating'=>array(), 'transportasi'=>array(), 'fasilitas'=>array(), 'jarak'=>array());
        for($i=0; $i<count($dataTerbobot);$i++){
            $matriks_ideal['biaya_masuk'][$i] = $dataTerbobot[$i]['biaya_masuk'];
            $matriks_ideal['rating'][$i] = $dataTerbobot[$i]['rating'];
            $matriks_ideal['transportasi'][$i] = $dataTerbobot[$i]['transportasi'];
            $matriks_ideal['fasilitas'][$i] = $dataTerbobot[$i]['fasilitas'];
            $matriks_ideal['jarak'][$i] = $dataTerbobot[$i]['jarak'];
        }

        for($i=0; $i<count($kriteria_seq);$i++){
            if($kriteria_seq[$i]['sifat']=='benefit'){
                $matriks_ideal_positif[$kriteria_seq[$i]['kode']] = max($matriks_ideal[$kriteria_seq[$i]['kode']]);
            } else {
                $matriks_ideal_positif[$kriteria_seq[$i]['kode']] = min($matriks_ideal[$kriteria_seq[$i]['kode']]);
            }
        }
        return $matriks_ideal_positif;
    }

    function getMatriksIdealNegatif($dataTerbobot, $kriteria_seq)
    {
        $matriks_ideal_negatif = array('biaya_masuk'=>0, 'rating'=>0, 'transportasi'=>0, 'fasilitas'=>0, 'jarak'=>0);
        $matriks_ideal = array('biaya_masuk'=>array(), 'rating'=>array(), 'transportasi'=>array(), 'fasilitas'=>array(), 'jarak'=>array());
        for($i=0; $i<count($dataTerbobot);$i++){
            $matriks_ideal['biaya_masuk'][$i] = $dataTerbobot[$i]['biaya_masuk'];
            $matriks_ideal['rating'][$i] = $dataTerbobot[$i]['rating'];
            $matriks_ideal['transportasi'][$i] = $dataTerbobot[$i]['transportasi'];
            $matriks_ideal['fasilitas'][$i] = $dataTerbobot[$i]['fasilitas'];
            $matriks_ideal['jarak'][$i] = $dataTerbobot[$i]['jarak'];
        }

        for($i=0; $i<count($kriteria_seq);$i++){
            if($kriteria_seq[$i]['sifat']=='benefit'){
                $matriks_ideal_negatif[$kriteria_seq[$i]['kode']] = min($matriks_ideal[$kriteria_seq[$i]['kode']]);
            } else {
                $matriks_ideal_negatif[$kriteria_seq[$i]['kode']] = max($matriks_ideal[$kriteria_seq[$i]['kode']]);
            }
        }
        return $matriks_ideal_negatif;
    }

    function getJarakSolusiIdeal($dataTerbobot, $idealPositif, $idealNegatif, $kriteria_seq)
    {
        $matriks_jarak_solusi = array();
        $jarak_positif = array();
        $jarak_negatif = array();
        for($i=0; $i<count($dataTerbobot);$i++){
            $jarak_positif[] = 0;
            $jarak_negatif[] = 0;
            for($y=0; $y<count($kriteria_seq);$y++){
                $jarak_positif[$i] += pow(($idealPositif[$kriteria_seq[$y]['kode']]-$dataTerbobot[$i][$kriteria_seq[$y]['kode']]), 2);
                $jarak_negatif[$i] += pow(($dataTerbobot[$i][$kriteria_seq[$y]['kode']]-$idealNegatif[$kriteria_seq[$y]['kode']]), 2);
            }
            $jarak_positif[$i] = sqrt($jarak_positif[$i]);
            $jarak_negatif[$i] = sqrt($jarak_negatif[$i]);
            $data = array(
                    "id_pantai"=>$dataTerbobot[$i]['id_pantai'],
                    "nama_pantai"=>$dataTerbobot[$i]['nama_pantai'],
                    "biaya_masuk"=>$dataTerbobot[$i]['biaya_masuk'],
                    "rating"=>$dataTerbobot[$i]['rating'],
                    "transportasi"=>$dataTerbobot[$i]['transportasi'],
                    "fasilitas"=>$dataTerbobot[$i]['fasilitas'],
                    "jarak"=>$dataTerbobot[$i]['jarak'],
                    "D+"=>$jarak_positif[$i],
                    "D-"=>$jarak_negatif[$i],
                    "jarak_solusi"=>$jarak_negatif[$i]/($jarak_negatif[$i]+$jarak_positif[$i])
                );
            $matriks_jarak_solusi[] = $data;
        }
        return $matriks_jarak_solusi;
    }

    function getRanking($dataSolusi)
    {
        for($i=0; $i<count($dataSolusi); $i++){
            $temp = array();
            $temp = $dataSolusi[$i];
            $j = $i-1;
            while ($j>=0 && $dataSolusi[$j]['jarak_solusi'] < $temp['jarak_solusi']) {
                $dataSolusi[$j+1] = $dataSolusi[$j];
                $j--;
            }
            $dataSolusi[$j+1] = $temp;
        }
        return $dataSolusi;
    }
?>