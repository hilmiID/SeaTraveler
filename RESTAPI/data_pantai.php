<?php 
    include 'koneksi.php';

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

    $query_kriteria = mysqli_query($con, "SELECT * FROM `table_kriteria`");
    $kriteria = $kriteria_seq = array();
    while ($row = mysqli_fetch_array($query_kriteria)){
        $kriteria[$row['kode']] = $row;
        $kriteria_seq[] = $row;
    }
    echo $kriteria_seq[0]['sifat'];
?>
<html>
<head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCwJ2Vepe9L2Miuh7QH87SR_RItIXHlX6Q&libraries=places"></script>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" id="bootstrap-css" rel="stylesheet" />
</head>
<body>
<div class="container">
<div class="row">
<div class="jumbotron">
<h1>Nilai Matriks Awal</h1>
</div>

<div class="row">
<table class="table">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Pantai</th>
        <th>Biaya Masuk</th>
        <th>Rating</th>
        <th>Transportasi</th>
        <th>Fasilitas</th>
        <th>Latitude</th>
        <th>Longitude</th>
        <th>Jarak</th>
      </tr>
    </thead>
    <tbody>
        <?php 
            $data_awal_penilaian = array();
            while ($row = mysqli_fetch_array($query)){
                $query_jumlah_transportasi = mysqli_query($con, "SELECT COUNT(jenis_transportasi) AS transportasi FROM `table_transportasi` INNER JOIN table_relasi_transportasi ON table_relasi_transportasi.id_transportasi = table_transportasi.id_transportasi INNER JOIN table_pantai ON table_relasi_transportasi.id_pantai = table_pantai.id_pantai WHERE table_pantai.id_pantai = ".$row['id_pantai']);
                $query_jumlah_fasilitas = mysqli_query($con, "SELECT COUNT(jenis_fasilitas) AS fasilitas FROM `table_fasilitas` INNER JOIN table_relasi_fasilitas ON table_relasi_fasilitas.id_fasilitas = table_fasilitas.id_fasilitas INNER JOIN table_pantai ON table_relasi_fasilitas.id_pantai = table_pantai.id_pantai WHERE table_pantai.id_pantai = ".$row['id_pantai']);
                $query_transportasi = mysqli_query($con, "SELECT (jenis_transportasi) AS transportasi FROM `table_transportasi` INNER JOIN table_relasi_transportasi ON table_relasi_transportasi.id_transportasi = table_transportasi.id_transportasi INNER JOIN table_pantai ON table_relasi_transportasi.id_pantai = table_pantai.id_pantai WHERE table_pantai.id_pantai = ".$row['id_pantai']);
                $query_fasilitas = mysqli_query($con, "SELECT (jenis_fasilitas) AS fasilitas FROM `table_fasilitas` INNER JOIN table_relasi_fasilitas ON table_relasi_fasilitas.id_fasilitas = table_fasilitas.id_fasilitas INNER JOIN table_pantai ON table_relasi_fasilitas.id_pantai = table_pantai.id_pantai WHERE table_pantai.id_pantai = ".$row['id_pantai']);
                $jumlah_transportasi = mysqli_fetch_array($query_jumlah_transportasi);
                $jumlah_fasilitas = mysqli_fetch_array($query_jumlah_fasilitas);
                $transportasi = "";
                $fasilitas = "";
                while ($row_transportasi = mysqli_fetch_array($query_transportasi)) {
                    $transportasi .= $row_transportasi['transportasi'].", ";
                }

                while ($row_fasilitas = mysqli_fetch_array($query_fasilitas)) {
                    $fasilitas .= $row_fasilitas['fasilitas'].", ";
                }

                $jarak = GetDrivingDistance("-7.949786", $row['latitude'] ,"112.617542", $row['longitude']);
                $data = array(
                    "id_pantai"=>$row['id_pantai'],
                    "nama_pantai"=>$row['nama_pantai'],
                    "biaya_masuk"=>$row['biaya_masuk'],
                    "rating"=>$row['rating'],
                    "transportasi"=>$jumlah_transportasi['transportasi'],
                    "fasilitas"=>$jumlah_fasilitas['fasilitas'],
                    "jarak"=>$jarak['distance'] 
                );
                echo "<tr>";
                echo "<td>".$row['id_pantai']."</td>"; 
                echo "<td>".$row['nama_pantai']."</td>";
                echo "<td>".$row['biaya_masuk']."</td>";
                echo "<td>".$row['rating']."</td>";
                echo "<td>".$transportasi."</td>";
                echo "<td>".$fasilitas."</td>";
                echo "<td>".$row['latitude']."</td>";
                echo "<td>".$row['longitude']."</td>";
                echo "<td>".$jarak['distance']."</td>";
                echo "</tr>";
                $data_awal_penilaian[] = $data;
      
        } ?> 
        
    </tbody>
</table>
</div>

<div class="jumbotron">
<h1>Nilai Matriks Ternormalisasi</h1>
</div>

<div class="row">
<table class="table">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Pantai</th>
        <th>Biaya Masuk</th>
        <th>Rating</th>
        <th>Transportasi</th>
        <th>Fasilitas</th>
        <th>Jarak</th>
      </tr>
    </thead>
    <tbody>
        <?php 
            $data_ternormalisasi = getKeputusanTernormalisasi($data_awal_penilaian);
            for($i=0; $i<count($data_ternormalisasi);$i++){
                echo "<tr>";
                echo "<td>".$data_ternormalisasi[$i]['id_pantai']."</td>"; 
                echo "<td>".$data_ternormalisasi[$i]['nama_pantai']."</td>";
                echo "<td>".$data_ternormalisasi[$i]['biaya_masuk']."</td>";
                echo "<td>".$data_ternormalisasi[$i]['rating']."</td>";
                echo "<td>".$data_ternormalisasi[$i]['transportasi']."</td>";
                echo "<td>".$data_ternormalisasi[$i]['fasilitas']."</td>";
                echo "<td>".$data_ternormalisasi[$i]['jarak']."</td>";
                echo "</tr>";
            }

        ?> 
        
    </tbody>
</table>
</div>

<div class="jumbotron">
<h1>Nilai Matriks Ternormalisasi Terbobot</h1>
</div>

<div class="row">
<table class="table">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Pantai</th>
        <th>Biaya Masuk</th>
        <th>Rating</th>
        <th>Transportasi</th>
        <th>Fasilitas</th>
        <th>Jarak</th>
      </tr>
    </thead>
    <tbody>
        <?php 
            $data_terbobot = getKeputusanTerbobot(getKeputusanTernormalisasi($data_awal_penilaian), $kriteria);
            for($i=0; $i<count($data_terbobot);$i++){
                echo "<tr>";
                echo "<td>".$data_terbobot[$i]['id_pantai']."</td>"; 
                echo "<td>".$data_terbobot[$i]['nama_pantai']."</td>";
                echo "<td>".$data_terbobot[$i]['biaya_masuk']."</td>";
                echo "<td>".$data_terbobot[$i]['rating']."</td>";
                echo "<td>".$data_terbobot[$i]['transportasi']."</td>";
                echo "<td>".$data_terbobot[$i]['fasilitas']."</td>";
                echo "<td>".$data_terbobot[$i]['jarak']."</td>";
                echo "</tr>";
            }

        ?> 
        
    </tbody>
</table>
</div>

<div class="jumbotron">
<h1>Matriks ideal positif</h1>
</div>

<div class="row">
<table class="table">
    <thead>
      <tr>
        <th>Kriteria</th>
      </tr>
      <tr>
          <?php 
            for($i=0; $i<count($kriteria_seq);$i++){
                echo "<th>".$kriteria_seq[$i]['nama_kriteria']."</th>";
            }
          ?>
      </tr>
    </thead>
    <tbody>
        <tr>
            <?php  
                $matriks_ideal_positif = getMatriksIdealPositif($data_terbobot, $kriteria_seq);
                for($i=0; $i<count($kriteria_seq);$i++){
                    echo "<td>".$matriks_ideal_positif[$kriteria_seq[$i]['kode']]."</td>";
                }
            ?>
        </tr>
    </tbody>
</table>
</div>

<div class="jumbotron">
<h1>Matriks ideal negatif</h1>
</div>

<div class="row">
<table class="table">
    <thead>
      <tr>
        <th>Kriteria</th>
      </tr>
      <tr>
          <?php 
            for($i=0; $i<count($kriteria_seq);$i++){
                echo "<th>".$kriteria_seq[$i]['nama_kriteria']."</th>";
            }
          ?>
      </tr>
    </thead>
    <tbody>
        <tr>
            <?php  
                $matriks_ideal_negatif = getMatriksIdealNegatif($data_terbobot, $kriteria_seq);
                for($i=0; $i<count($kriteria_seq);$i++){
                    echo "<td>".$matriks_ideal_negatif[$kriteria_seq[$i]['kode']]."</td>";
                }
            ?>
        </tr>
    </tbody>
</table>
</div>

<div class="jumbotron">
<h1>Jarak Ideal Positif Negatif</h1>
</div>

<div class="row">
<table class="table">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Pantai</th>
        <th>D+</th>
        <th>D-</th>
        <th>Jarak Solusi</th>
      </tr>
    </thead>
    <tbody>
        <?php 
            $matriks_solusi = getJarakSolusiIdeal($data_terbobot, $matriks_ideal_positif, $matriks_ideal_negatif, $kriteria_seq);
            for($i=0; $i<count($matriks_solusi);$i++){
                echo "<tr>";
                echo "<td>".$matriks_solusi[$i]['id_pantai']."</td>"; 
                echo "<td>".$matriks_solusi[$i]['nama_pantai']."</td>";
                echo "<td>".$matriks_solusi[$i]['D+']."</td>";
                echo "<td>".$matriks_solusi[$i]['D-']."</td>";
                echo "<td>".$matriks_solusi[$i]['jarak_solusi']."</td>";
                echo "</tr>";
            }

        ?> 
        
    </tbody>
</table>
</div>

<div class="jumbotron">
<h1>Ranking</h1>
</div>

<div class="row">
<table class="table">
    <thead>
      <tr>
        <th>Ranking</th>
        <th>Nama Pantai</th>
        <th>Jarak Solusi</th>
      </tr>
    </thead>
    <tbody>
        <?php 
            $Ranking = getRanking($matriks_solusi);
            for($i=0; $i<count($matriks_solusi);$i++){
                echo "<tr>";
                echo "<td>".($i+1)."</td>"; 
                echo "<td>".$Ranking[$i]['nama_pantai']."</td>";
                echo "<td>".$Ranking[$i]['jarak_solusi']."</td>";
                echo "</tr>";
            }

        ?> 
        
    </tbody>
</table>
</div>
<?php 
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
                        "transportasi":"'.str_replace($char,'`',strip_tags($transportasi)).'",
                        "fasilitas":"'.str_replace($char,'`',strip_tags($fasilitas)).'",
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
</div>
</div>
</html>


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