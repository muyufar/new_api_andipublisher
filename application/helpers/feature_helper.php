<?php


function createID($db, $search, $table, $kode)
{
	$id_primary = $db->query("SELECT max($search) as maxKode FROM $table");
	$id_primary = $id_primary->row_array();
	$id_primary = $id_primary['maxKode'];

	if (substr($id_primary, 2, 8) != date('Ymd')) {
		$noUrut = 1;
	} else {
		$noUrut = (int) substr($id_primary, 10, 10);
		if ($noUrut == 9999999999) {
			$noUrut = 1;
		} else {
			$noUrut++;
		}
	}
	$id_primary = $kode . date('Ymd') . sprintf("%010s", $noUrut);
	return $id_primary;
}

function id_ke_struk($string)
{
	$inisial = substr($string, 0, 2);
	$tgl = substr($string, 4, 6);
	$tgl = date_format(date_create($tgl), "dmy");
	$num = round(substr($string, 10));
	$no_nota = $inisial = $inisial . "_" . $tgl . "_" . $num;
	return $no_nota;
}

function genereate_referral($id_primary)
{

	$tgl_ymd = substr($id_primary, 4, 6);
	$tgl_d = date_format(date_create($tgl_ymd), "d");
	$tgl_y = date_format(date_create($tgl_ymd), "y");
	$tgl_m = date_format(date_create($tgl_ymd), "m");
	$num = round(substr($id_primary, 10));
	$random_word = substr(str_shuffle("WERTUPLKHFAXCVBNM"), 0, 1);

	switch ($tgl_m) {
		case '1':
			$bulan_enc = "A";
			break;
		case '2':
			$bulan_enc = "L";
			break;
		case '3':
			$bulan_enc = "E";
			break;
		case '4':
			$bulan_enc = "P";
			break;
		case '5':
			$bulan_enc = "Z";
			break;
		case '6':
			$bulan_enc = "M";
			break;
		case '7':
			$bulan_enc = "F";
			break;
		case '8':
			$bulan_enc = "K";
			break;
		case '9':
			$bulan_enc = "W";
			break;
		case '10':
			$bulan_enc = "U";
			break;
		case '11':
			$bulan_enc = "X";
			break;
		case '12':
			$bulan_enc = "N";
			break;
	}

	$createRefferalID = $bulan_enc . $tgl_y . $num . $random_word . $tgl_d;

	return $createRefferalID;
}

function nomor_plus_62($data)
{
	if (!function_exists('nomor_plus_62')) {
		function nomor_plus_62($nomor_telepon)
		{
			// Hapus semua karakter kecuali angka
			$nomor_telepon = preg_replace('/[^0-9]/', '', $nomor_telepon);

			// Periksa apakah nomor telepon sudah diawali dengan kode negara
			if (substr($nomor_telepon, 0, 1) === '0') {
				// Jika diawali dengan '0', ganti dengan kode 62
				$nomor_telepon = '62' . substr($nomor_telepon, 1);
			}

			return $nomor_telepon;
		}
	}
}
