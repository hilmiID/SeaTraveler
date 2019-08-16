<?php 
	include 'koneksi.php';

	$query = mysqli_query($con, "SELECT nama_pantai FROM `table_pantai`");

	$json = '{"daftar_pantai": [';
	$no = 1;

	// bikin looping dech array yang di fetch
	while ($row = mysqli_fetch_array($query)){

	//tanda kutip dua (") tidak diijinkan oleh string json, maka akan kita replace dengan karakter `
	//strip_tag berfungsi untuk menghilangkan tag-tag html pada string 
		$char ='"';

		$json .= 
		'{
			"nama_pantai":"'.$no.'. '.str_replace($char,'`',strip_tags($row['nama_pantai'])).'"
		},';
		$no++;
	}

	// buat menghilangkan koma diakhir array
	$json = substr($json,0,strlen($json)-1);

	$json .= ']}';

	// print json
	echo $json;
	
	mysqli_close($con);
?>