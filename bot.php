<?php
$token = ""; //Ganti dengan Token API yang diperoleh dari BotFather
$usernamebot=""; //nama bot yang diperoleh dari BotFather
define('BOT_TOKEN', $token); 

define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

$debug = false;

function exec_curl_request($handle)
{
    $response = curl_exec($handle);

    if ($response === false) {
        $errno = curl_errno($handle);
        $error = curl_error($handle);
        error_log("Curl returned error $errno: $error\n");
        curl_close($handle);

        return false;
    }

    $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
    curl_close($handle);

    if ($http_code >= 500) {
        // do not wat to DDOS server if something goes wrong
    sleep(10);

        return false;
    } elseif ($http_code != 200) {
        $response = json_decode($response, true);
        error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
        if ($http_code == 401) {
            throw new Exception('Invalid access token provided');
        }

        return false;
    } else {
        $response = json_decode($response, true);
        if (isset($response['description'])) {
            error_log("Request was successfull: {$response['description']}\n");
        }
        $response = $response['result'];
    }

    return $response;
}

function apiRequest($method, $parameters = null)
{
    if (!is_string($method)) {
        error_log("Method name must be a string\n");

        return false;
    }

    if (!$parameters) {
        $parameters = [];
    } elseif (!is_array($parameters)) {
        error_log("Parameters must be an array\n");

        return false;
    }

    foreach ($parameters as $key => &$val) {
        // encoding to JSON array parameters, for example reply_markup
    if (!is_numeric($val) && !is_string($val)) {
        $val = json_encode($val);
    }
    }
    $url = API_URL.$method.'?'.http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

    return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters)
{
    if (!is_string($method)) {
        error_log("Method name must be a string\n");

        return false;
    }

    if (!$parameters) {
        $parameters = [];
    } elseif (!is_array($parameters)) {
        error_log("Parameters must be an array\n");

        return false;
    }

    $parameters['method'] = $method;

    $handle = curl_init(API_URL);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
    curl_setopt($handle, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    return exec_curl_request($handle);
}

// jebakan token, klo ga diisi akan mati
if (strlen(BOT_TOKEN) < 20) {
    die(PHP_EOL."1182136404:AAEQzSbAUpLfJFJA7uNO89nfIyLEBpFvYak");
}

function getUpdates($last_id = null)
{
    $params = [];
    if (!empty($last_id)) {
        $params = ['offset' => $last_id + 1, 'limit' => 1];
    }
  //echo print_r($params, true);
  return apiRequest('getUpdates', $params);
}

// matikan ini jika ingin bot berjalan
//die('baca dengan teliti yak!');






// ----------- pantengin mulai ini
function sendMessage($idpesan, $idchat, $pesan)
{
    $data = [
    'chat_id'             => $idchat,
    'text'                => $pesan,
    'parse_mode'          => 'Markdown',
    'reply_to_message_id' => $idpesan,
  ];

    return apiRequest('sendMessage', $data);
}

function processMessage($message)
{
    global $database;
    if ($GLOBALS['debug']) {
        print_r($message);
    }

    if (isset($message['message'])) {
            
        $sumber = $message['message'];
        $idpesan = $sumber['message_id'];
        $idchat = $sumber['chat']['id'];
        
        $username = $sumber["from"]["username"];
        $nama = $sumber['from']['first_name'];
        $iduser = $sumber['from']['id'];
        

        if (isset($sumber['text'])) {
            $pesan = $sumber['text'];

            if (preg_match("/^\/view_(\d+)$/i", $pesan, $cocok)) {
                $pesan = "/view $cocok[1]";
            }

            if (preg_match("/^\/hapus_(\d+)$/i", $pesan, $cocok)) {
                $pesan = "/hapus $cocok[1]";
            }

     // print_r($pesan);

      $pecah2 = explode(' ', $pesan, 3);
            $katake1 = strtolower($pecah2[0]); //untuk command
            $katake2 = strtolower($pecah2[1]); // kata pertama setelah command
            $katake3 = strtolower($pecah2[2]); // kata kedua setelah command
            
      $pecah = explode(' ', $pesan, 2);
            $katapertama = strtolower($pecah[0]); //untuk command
      
      $pisah = explode(' ,, ', $pesan, 5);
            $kata1 = strtolower($pisah[0]); //command
            $kata2 = strtolower($pisah[1]); //kata pertama setelah command
            $kata3 = strtolower($pisah[2]); //kata kedua setelah command
            $kata4 = strtolower($pisah[3]);
            $kata5 = strtolower($pisah[4]);
            
        switch ($katapertama) {
        case '/start': 
		case '/start@namabot':
          $text = "Selamat datang kak $nama ! \n";
          $text .= "Untuk bantuan ketik: /help";
          break;

        case '/help': 
        case '/help@namabot':
          $text = "Halo kak $nama , ada yang bisa dibantu? 😁 \n\n";
		  $text .= "/start : untuk memulai bot\n";
          $text .= "/help : info bantuan ini\n";	 
          $text .= "/hadir : untuk presensi kehadiran\n";
          $text .= "/balikkanan : untuk presensi pulang\n";
          $text .= "/ijin : untuk jika ijin \n";
          $text .= "/time : info waktu sekarang";
          $text .= "\n \n /help2 : Task management";
          break;
          
        case '/help2': 
        case '/help2@namabot':
          $text = "🗂 *Task Management* \n\n";
		  $text .= "Tentang Task \n";
		  $text .= "/cektask : untuk melihat daftar task \n";
          $text .= "/tambahtask : menambah task baru \n";
          $text .= "/taskstatus : update status task \n";
          $text .= "/taskprogress : update presentase progress task \n \n";
          $text .= "Tentang Sub-Task \n";
          $text .= "/ceksubtask : untuk melihat daftar subtask \n";
          $text .= "/tambahsubtask : menambah subtask baru \n";
          $text .= "/subtaskstatus : update status task \n";
          $text .= "/subtaskprogress : update presentase progress subtask \n";
          $text .= "\n /help3 : Progstart & progend \n";
          break;
          
        case '/help3': 
        case '/help3@namabot':
          $text = "🗳 *Progstart & Progend* \n\n";
          $text .= "Tentang Progstart & Progend \n";
          $text .= "/lihatprogstart : lihat daftar progstart hari ini \n";
          $text .= "/progstart : memulai pengerjaan \n";
          $text .= "/progend : mengakhiri pengerjaan \n";
          $text .= "/panduanprogstart : informasi alur untuk progstart & progend \n";
        break;
        
        case '/panduanprogstart': 
        case '/panduanprogstart@namabot':
          $text = "📑 *Alur Progstart & Progend* \n\n";
          $text .= "1. Apakah sudah ada task baru? \n";
          $text .= "2. Jika belum ada buat task baru dengan /tambahtask  \n";
          $text .= "3. Sudah ada subtask?  \n";
          $text .= "4. Jika belum ada, buat subtask baru dengan /tambahsubtask \n";
          $text .= "5. Mulai mengerjakan dengan /progstart \n";
          $text .= "6. Akhiri dengan /progend \n";
          //$text .= "7.  \n";
        break;
		
		case '/pilihan':
		case '/pilihan@namabot':
		  $text = "pilihan 1" | $callback_data= "1";
		    
		    
		    break;
		
		case '/ijin':
		case '/ijin@namabot':
		  if (isset($pecah2[1])){
		      $search = $pecah2[1]; //mengambil kata kedua
		      //include "koneksi.php";
		      
		      date_default_timezone_set("Asia/Jakarta");
		      $tanggalsekarang = date('d-m-Y');
		      $jamsekarang = date('H:i:s');
		      
		      include "koneksi.php";
		      
		      $simpan="UPDATE absen SET absen='ijin'
		               WHERE tanggal='$tanggalsekarang'
		               AND id_telegram='$iduser' ";
									
			  //mysql_query($simpan);
			  
			  if (mysqli_query($conn, $simpan))
			  {
                  $text = "Direkap dulu ya kak $nama 📝 ";
    		      $text .= "\n";
    		      $text .= "Selamat melanjutkan kegiatan";
              } else {
                  $text = '*ERROR:* _Data tidak bisa masuk_';
              }
		      
		  } else {
		      $text = '*ERROR:* _Jam tidak boleh kosong_';
			  $text .= "\n";
			  $text .= "Contoh format: /ijin `jam` `WFH`";
		  }
		    break;
		    
		case '/izin':
		case '/izin@namabot':
		  if (isset($pecah2[1])){
		      $search = $pecah2[1]; //mengambil kata kedua
		      //include "koneksi.php";
		      
		      date_default_timezone_set("Asia/Jakarta");
		      $tanggalsekarang = date('d-m-Y');
		      $jamsekarang = date('H:i:s');
		      
		      include "koneksi.php";
		      
		      $simpan="UPDATE absen SET absen='ijin'
		               WHERE tanggal='$tanggalsekarang'
		               AND id_telegram='$iduser' ";
									
			  //mysql_query($simpan);
			  
			  if (mysqli_query($conn, $simpan))
			  {
                  $text = "Direkap dulu ya kak $nama 📝 ";
    		      $text .= "\n";
    		      $text .= "Selamat melanjutkan kegiatan";
              } else {
                  $text = '*ERROR:* _Data tidak bisa masuk_';
              }
		      
		  } else {
		      $text = '*ERROR:* _Jam tidak boleh kosong_';
			  $text .= "\n";
			  $text .= "Contoh format: /ijin `jam` `WFH`";
		  }
		    break;    
		
		case '/presensi':
		case '/presensi@namabot':
		    if (isset($pecah2[1])){
		      //$cariuser = $pisah[1];
		      $caritanggal = $pecah2[1];
		      
              include "koneksi.php";

              $text = "Presensi pada tanggal $caritanggal :\n \n";
              $result = mysqli_query("SELECT * FROM absen WHERE tanggal='$caritanggal'");
              //$tampilkan = mysqli_fetch_array($result, MYSQL_NUM);
              $tbnama = $row[2];
              
              while ($row=mysqli_fetch_array($result, MYSQL_NUM))
              {
                $text .= "> $tbnama \n";
                //$text .= "\n";
              }
              $text .= "> $tbnama \n \n";
              $text .= "Selesai";
    		} else {
    		  $text = "*ERROR:* _Data belum bisa ditampilkan_"; 
    		}
    		
	    break;
		
		case '/cekbalikkanan':
		case '/balikkanan@namabot':
		    
		    include "koneksi.php";
		    $cekdatabalikkanan = "SELECT * FROM absen WHERE tanggal='$tanggalsekarang' AND id_telegram='$iduser'";
		    if(isset($cekdatabalikkanan))
		      {
		        $text = '*Data Sudah Ada';
			  } else {
			    $text = "Data Kosong";
			  }
		break;
		
		case '/balikkanan':
		case '/balikkanan@namabot':
		  if (isset($pecah2[1])){
		      $search = $pecah2[1]; //mengambil kata kedua
		      
		      include "koneksi.php";
		      $cekdatabalikkanan = "SELECT * FROM absen WHERE tanggal='$tanggalsekarang' AND id_telegram='$iduser'";
		      
		      if(isset($cekdatabalikkanan))
		      {
		        date_default_timezone_set("Asia/Jakarta");
		        $tanggalsekarang = date('d-m-Y');
		        $jamsekarang = date('H:i:s');
		      
		        $simpan="UPDATE absen SET jampulang='$jamsekarang'
		                 WHERE tanggal='$tanggalsekarang'
		                 AND id_telegram='$iduser' ";
			  
    			  //random greeting untuk pulang
    			  $goodbyegreeting = array(
                    "Terimakasih yaa untuk hari ini, kak $nama 😊 ",
                    "Sampai jumpa lagi kak $nama 😄 ",
                    "Farewell $nama 👋",
                    "Goodbye $nama 👋",
                    "Catch you later $nama 👋",
                    "Sayonaraa! 👋 ",
                    "Arigatou gozaimas, $nama senpai! 😁 ",
                    "Thanks a bunch $nama 😎 ");
                  shuffle($goodbyegreeting);
                  $showgoodbyegreeting = array_shift($goodbyegreeting);
    			  
    			  if (mysqli_query($conn, $simpan))
    			  {
                      $text = $showgoodbyegreeting;
                      //$text = "71 kak $nama 😄 ";
        		      //$text .= "\n";
        		      //$text .= "See you later";
                  } else {
                      $text = '*ERROR:* _Data tidak bisa masuk_';
                  }
		      } else {
		          $text = "*ERROR:* _Tidak bisa balik kanan karena belum hadir_";
		          $text .= "\n";
		          $text .= "Tulis `/hadir [jam] [keterangan]` terlebih dahulu";
		      }
		      
		  } else {
		      $text = '*ERROR:* _Jam pulang tidak boleh kosong_';
			  $text .= "\n";
			  $text .= "Contoh format: /hadir `jam pulang` `WFH`";
		  }
		    break;
		
		case '/jamkehadiran': 
		case '/jamkehadiran@namabot':
          date_default_timezone_set("Asia/Jakarta");
		  $waktusekarang = date('Y-m-d H:i:s');
          //$jamsekarang = date('H:i:s');
          
          include "koneksi.php";
          
          //cek jam masuk dan keterlambatan
          $cekwaktupresensi = mysqli_query($conn, "CALL spkehadiranbyidtelegram('$iduser')");
          //cek apakah ada query diterima
          $cekdatapresensi = mysqli_num_rows($cekwaktupresensi);
          
          if($cekdatapresensi > 0)
          {
            $datapresensi = mysqli_fetch_assoc($cekwaktupresensi);
            $waktuhadirseharusnya = $datapresensi['timemasuk'];

            //selisih waktu
            $setengahjam = date('00:30:00');
            $perhitungansetengahjam = strtotime($setengahjam);
            $setjam = date('H:i:s', $perhitungansetengahjam);
            $perhitunganjam = strtotime($waktuhadirseharusnya);
            $jam = date('H:i:s', $perhitunganjam);
            //$jampresensi = strtotime($waktuhadirseharusnya - $jam);
            $perhitunganjampresensiseharusnya = $perhitunganjam - $perhitungansetengahjam;
            //$jampresensiseharusnya = date('H:i:s', $perhitunganjampresensiseharusnya);
            $jampresensiseharusnya = date('H:i:s', $perhitunganjampresensiseharusnya / (60*60));
            
            if ($jamsekarang < $waktuhadirseharusnya)
               {
                //$pesanpresensi = "Kak $nama : $waktuhadirseharusnya \nPresensi minimal jam : $jampresensi";
                $pesanpresensi = "$perhitunganjam \n$jam \n$perhitungansetengahjam \n$setjam \n------\n$perhitunganjampresensiseharusnya \nshould be: $jampresensiseharusnya";
               } else {
                $pesanpresensi = "error bosqu";
               }
          } else {
            $pesanpresensi = "data presensi tidak ditemukan";
          }
          
          //pesan yang ditampilkan
          //$text = $showgreeting;
          //$text = "\n";
          $text = $pesanpresensi;
          
          break;
		
		case '/hadir':
		case '/hadir@namabot':
		  if (isset($pecah2[1])){
		      $jamhadir = $pecah2[1]; //mengambil kata kedua
		      $ket = $pecah2[2];
		      
		      date_default_timezone_set("Asia/Jakarta");
		      $tanggalsekarang = date('d-m-Y');
		      $jamsekarang = date('H:i:s');
		      
		      include "koneksi.php";
		      
		      $simpan="INSERT INTO absen
		              (id, nama, usernametelegram, id_telegram, absen, tanggal, jammasuk, jampulang)
		              VALUES 
			      	  (NULL, '$nama','$username','$iduser','hadir','$tanggalsekarang','$jamsekarang', '')";
			  
			  //random greeting selamat datang
			  $welcomegreeting = array(
                "Hai kak $nama , selamat beraktivitas hari ini!",
                //"Halo kak $nama , jangan lupa sarapan yaa",
                "Selamat datang kembali kak $nama, semangat!",
                "Hi $nama , ready for today experience?",
                //"Hai kak $nama , sudah sarapan pagi ini?", 
                "Ohayou gozaimasu $nama senpai! 😉",
                "Good morning $nama",
                "Glad to see you again $nama",
                "Delighted to see you again $nama",
                "Good to see you again $nama",
                "Wonderful to see you again $nama",
                "Welcome-back $nama",
                "Welcome in $nama",
                "Welcome to the house $nama",
                "Ohayou $nama kun! 😉 ",
                "Selamat pagi kak $nama");
              shuffle($welcomegreeting);
              $showgreeting = array_shift($welcomegreeting);
              
			  //save database jam masuk (presensi)						
			  if (mysqli_query($conn, $simpan))
			  {
			      //cek jam masuk dan keterlambatan
                  $cekwaktupresensi = mysqli_query($conn, "CALL spkehadiranbyidtelegram('$iduser')");
                  //cek apakah ada query diterima
                  $cekdatapresensi = mysqli_num_rows($cekwaktupresensi);
                  
                  if($cekdatapresensi > 0)
                  {
                      $datapresensi = mysqli_fetch_assoc($cekwaktupresensi);
                      $waktuhadirseharusnya = $datapresensi['timemasuk'];
                      if ($jamsekarang < $waktuhadirseharusnya)
                      {
                          $pesanpresensi = "Terima kasih sudah hadir tepat waktu.";
                      } else {
                          $pesanpresensi = "Kak $nama tercatat terlambat ya";
                      }
                  } else {
                      $pesanpresensi = "data presensi tidak ditemukan";
                  }
			      //pesan yang ditampilkan
                  $text = $showgreeting;
                  $text .= "\n";
                  $text .= $pesanpresensi;
              } else {
                  $text = '*ERROR:* _Data tidak bisa masuk_';
              }

		      
		  } else {
		      $text = '*ERROR:* _Jam kehadiran tidak boleh kosong_';
			  $text .= "\n";
			  $text .= "Contoh format: /hadir `jam hadir` `WFH`";
		  }
					
          break;
		
		case '/cekproject':
		case '/cekproject@namabot':
		  include "koneksi-taskman.php";
		  $tampilquery = mysqli_query($conn, "CALL splihatdaftarproject;");
		  
		  $no = 1;
              $text = "List project \n \n";
              while($data = mysqli_fetch_row($tampilquery))
               {
                $text .= $no;
                $text .= ". ";
                $text .= $data[1];
                $text .= " (";
                $text .= $data[0];
                $text .= ") ";
                $text .= " - ";
                $text .= $data[2];
                $text .= "\n \n";
                $no++;
                }
		  
		  break; 
		
		case '/cekteam':
		case '/cekteam@namabot':
              include "koneksi-taskman.php";
              $tampilquery = mysqli_query($conn, "CALL splihatdaftarteam");
              
              $no = 1;
              $text = "List Dako Team\n \n";
              while($data = mysqli_fetch_row($tampilquery))
               {
                $text .= $no;
                $text .= ". ";
                $text .= $data[1];
                $text .= "\n";
                $no++;
                }
              mysqli_free_result($tampilquery);
              $text .= "Selesai";
		break;
		
		case '/cektask':
		case '/cektask@namabot':
		    if (isset($pecah2[1])){
		      $carinama = $pecah2[1];
		      
              include "koneksi-taskman.php";
              $tampilquery = mysqli_query($conn, "CALL spcaritaskbynama('$carinama');");
              
              $no = 1;
              $text = "List task $carinama: \n \n";
              while($data = mysqli_fetch_row($tampilquery))
               {
                $text .= $no;
                $text .= ". ";
                $text .= $data[1];
                $text .= " (";
                $text .= $data[0];
                $text .= ") ";
                $text .= " - ";
                $text .= $data[2];
                $text .= " ( ";
                $text .= $data[3];
                //$text .= "";
                //$text .= $data[0];
                $text .= " )";
                //$text .= " /taskstatus";
                //$text .= $data[0];
                $text .= "\n \n";
                $no++;
                }
              mysqli_free_result($tampilquery);
              $text .= "Selesai";
    		} else {
    		  $text = "⚠️ _Data belum bisa ditampilkan_";
    		  $text .= "\n \n Format melihat list task: \n";
    	      $text .= "/cektask [nama] \n \n Contoh: \n /cektask $nama";
    	      //$text .= "\n \n Daftar nama: \n";
    	      
    	      include "koneksi-taskman.php";
              $tampilteam = mysqli_query($conn, "CALL splihatdaftarteam");
              
              $noteam .= 1;
              $text .= "\n \n Daftar nama: \n";
              while($datateam = mysqli_fetch_row($tampilteam))
               {
                $text .= $noteam;
                $text .= ". ";
                $text .= $datateam[1];
                $text .= "\n";
                $noteam++;
                }
              mysqli_free_result($tampilteam);
              //$text .= "Selesai";
    		}
    	  break;
    	  
    	case '/ceksubtask':
		case '/ceksubtask@namabot':
		    if (isset($pecah2[1])){
		      $carinama = $pecah2[1];
		      
              include "koneksi-taskman.php";
              $tampilquery = mysqli_query($conn, "CALL spcarisubtaskbynama('$carinama');");
              
              $no = 1;
              $text = "List subtask $carinama: \n \n";
              while($data = mysqli_fetch_row($tampilquery))
               {
                $text .= $no;
                $text .= ". ";
                $text .= $data[1];
                $text .= " - ";
                $text .= $data[2];
                $text .= " (";
                $text .= $data[0];
                $text .= ") ";
                $text .= " - ";
                $text .= $data[3];
                $text .= " / ";
                $text .= $data[4];
                $text .= "\n \n";
                $no++;
                }
              mysqli_free_result($tampilquery);
              $text .= "Selesai";
    		} else {
    		  $text = "⚠️ _Data belum bisa ditampilkan_";
    		  $text .= "\n \n Format melihat list subtask: \n";
    	      $text .= "/ceksubtask [nama] \n \n Contoh: \n /ceksubtask $nama";
    	      //$text .= "\n \n Daftar nama: \n";
    	      
    	      include "koneksi-taskman.php";
              $tampilteam = mysqli_query($conn, "CALL splihatdaftarteam");
              
              $noteam .= 1;
              $text .= "\n \n Daftar nama: \n";
              while($datateam = mysqli_fetch_row($tampilteam))
               {
                $text .= $noteam;
                $text .= ". ";
                $text .= $datateam[1];
                $text .= "\n";
                $noteam++;
                }
              mysqli_free_result($tampilteam);
              //$text .= "Selesai";
    		}
    	  break;
    	
    	case '/lihatprogstart':
    	case '/lihatprogstart@namabot':
    	    
    	    date_default_timezone_set("Asia/Jakarta");
		    //$tanggalsekarang = date('d-m-Y');
    	    $tanggalsekarang = date('Y-m-d');
    	    
    	    include "koneksi-taskman.php";
            $tampilquery = mysqli_query($conn, "CALL spcaridailyprogstartbytanggal('$tanggalsekarang');");
    	    
    	    $no = 1;
            $text = "List progstart tanggal $tanggalsekarang \n \n";
             
            while($data = mysqli_fetch_row($tampilquery))
            {
             $text .= $no;
             $text .= ". ";
             //$text .= $data[1];
             //$text .= " - ";
             //$text .= $data[2];
             //$text .= $data[3];
             //$text .= " / ";
             //$text .= $data[4];
             //$text .= " | ";
             $text .= $data[5];
             $text .= " | ";
             $text .= $data[6];
             $text .= " | ";
             $text .= $data[7];
             $text .= " | ";
             $text .= $data[8];
             $text .= " (";
             $text .= $data[0];
             $text .= ") ";
             $text .= "\n";
             $no++;
            }
    	    
    	    break;
    	 
    	case '/tambahproject':
		case '/tambahproject@namabot':
		    //$text = "Coming soon \n \n";
		    
		    if (isset($pisah[1])){
		        $namaproject = $pisah[1];
		        $idproject = $pisah[2];
		        $deskripsiproject = $pisah[3];
		    
		    include "koneksi-taskman.php";
		        $querytambahproject = mysqli_query($conn, "CALL sptambahprojectbaru('$idproject', '$namaproject', '$deskripsiproject');");
		        $tampilquery = mysqli_query($conn, "CALL splihatdaftarproject;");
		        
		        $text = "✅ _Project baru berhasil ditambahkan_ \n \n";
		        $no = 1;
                $text = "List project \n \n";
                while($data = mysqli_fetch_row($tampilquery))
                   {
                    $text .= $no;
                    $text .= ". ";
                    $text .= $data[1];
                    $text .= " (";
                    $text .= $data[0];
                    $text .= ") ";
                    $text .= " - ";
                    $text .= $data[2];
                    $text .= "\n \n";
                    $no++;
                    }
		        
		    }else{
    	        $text = "⚠️ _Tidak bisa menambah project baru_";
    	        $text .= "\n \n Format tambah project baru: \n";
    	        $text .= "/tambahproject ,, [nama project] ,, [id project] ,, [deskripsi singkat] \n \n Contoh: \n /tambahproject ,, Project klien 1 ,, PK01 ,, Pemkab Malang";
    	        $text .= "\n \nnb: \n";
    	        $text .= "Id project         : max 4 karakter \n";
    	        $text .= "Nama project : max 25 karakter \n";
    	        //$text .= "3. Business Area : BA \n";
    	    }
		    
		    break;   
    	  
    	case '/tambahtask':
    	case '/tambahtask@namabot':
    	    if (isset($pisah[1])){
		        $namatask = $pisah[1];
		        $idproject = $pisah[2]; //ambil kata kedua
		        
		        //if ($namaproject == "DBC") {
		        //    $idproject = "01DK";
		        //} else if ($namaproject == "HAOL") {
		        //    $idproject = "02HL";
		        //} else if ($namaproject == "BA") {
		        //    $idproject = "03BA";
		        //} else {
		        //    $idproject = "01DK";
		        //}
		        
		        include "koneksi-taskman.php";
		        //$querytambahtask = mysqli_query($conn, "CALL sptambahtask('$namatask', '$iduser', '$idproject');");
		        $querytambahtask = mysqli_query($conn, "INSERT INTO `tbtask` (`intidtask`, `vcnamatask`, `vcprogress`, `vcstatus`, `vcpic`, `vcidproj`) VALUES (NULL, '$namatask', '0%', '10LIST', '$iduser', '$idproject');");
		        $querylihattaskbaru = mysqli_query($conn, "CALL splihattaskbaruditambah('$iduser');");
		        
		        $text = "✅ _Task baru berhasil ditambahkan_ \n \n";
		        
		        while($data = mysqli_fetch_row($querylihattaskbaru))
                {
                 $text .= "ID Task : ";
                 $text .= $data[0];
                 $text .= " \n";
                 $text .= "Project : ";
                 $text .= $data[1];
                 $text .= " \n";
                 $text .= "Task      : ";
                 $text .= $data[2];
                 $text .= " \n";
                 $text .= "Status  : ";
                 $text .= $data[4];
                 $text .= " ( ";
                 $text .= $data[3];
                 $text .= " ) \n \n";
                }
		        
		        $text .= "Lihat list task: /cektask $nama";
		        
    	    }else{
    	        $text = "⚠️ _Tidak bisa menambah task baru_";
    	        $text .= "\n \n Format tambah task baru: \n";
    	        $text .= "/tambahtask ,, [nama task] ,, [id project] \n \n Contoh: \n /tambahtask ,, Landing Page klien Dako 1 ,, 01DK";
    	        //$text .= "\n \n Cek list project: /cekproject \n";
    	        
    	        include "koneksi-taskman.php";
    	        $tampilproject = mysqli_query($conn, "CALL splihatdaftarproject;");
		        $no = 1;
                  $text .= "\n \nList project: \n";
                  while($dataproject = mysqli_fetch_row($tampilproject))
                   {
                    $text .= $no;
                    $text .= ". ";
                    $text .= $dataproject[1];
                    $text .= " (";
                    $text .= $dataproject[0];
                    $text .= ") ";
                    $text .= "\n";
                    $no++;
                    }
        	    }
        	  break;
    	  
    	case '/tambahsubtask':
    	case '/tambahsubtask@namabot':
    	    
    	    if (isset($pisah[1])){
		        $idtask = $pisah[1];
		        $namasubtask = $pisah[2];
		        
		        include "koneksi-taskman.php";
		        $querytambahtask = mysqli_query($conn, "CALL sptambahsubtask('$idtask', '$namasubtask', '$iduser');");
		        $querylihatsubtaskbaru = mysqli_query($conn, "CALL splihatsubtaskbaruditambah('$iduser');");
		        
		        $text = "✅ _Subtask baru berhasil ditambahkan_ \n \n";
		        
		        
		        while($data = mysqli_fetch_row($querylihatsubtaskbaru))
                {
                 $text .= "ID subtask : ";
                 $text .= $data[0];
                 $text .= " \n";
                 $text .= "Task             : ";
                 $text .= $data[1];
                 $text .= " ( ";
                 $text .= $data[2];
                 $text .= " )";
                 $text .= " \n";
                 $text .= "Subtask      : ";
                 $text .= $data[3];
                 $text .= " \n";
                 $text .= "Status         : ";
                 $text .= $data[5];
                 $text .= " ( ";
                 $text .= $data[4];
                 $text .= " ) \n \n";
                }
		        
		        $text .= "Lihat list subtask: /ceksubtask $nama";
		        
    	    }else{
    	        $text = "⚠️ _Tidak bisa menambah subtask baru_";
    	        $text .= "\n \n Format tambah task baru: \n";
    	        $text .= "/tambahsubtask ,, [id task] ,, [nama subtask] \n \n Contoh: \n /tambahsubtask ,, 6 ,, Konten landing page klien";
    	        $text .= "\n \n Cek id task: \n";
    	        $text .= "/cektask $nama \n";
    	    }
    	    
    	    break;
		
		case '/taskprogress':
		case '/progresstask@namabot':
		    if (isset($pisah[1])){
		        $idtask = $pisah[1];
		        $progresstask = $pisah[2];
		        
		        include "koneksi-taskman.php";
		        $querytaskprogress = mysqli_query($conn, "CALL spupdatetaskprogress('$progresstask', '$idtask');");
		        
		        $text = "✅ _Progress task telah berhasil diupdate_ \n \n";
		        $text .= "Lihat list task: /cektask $nama";
		        
    	    }else{
    	        $text = "⚠️ _Tidak bisa menambah task baru_";
    	        $text .= "\n \n Format update progress task: \n";
    	        $text .= "/progresstask ,, [id task] ,, [progress task] \n \n Contoh: \n /progresstask ,, 6 ,, 75%";
    	    }
		    break;
		    
		case '/subtaskprogress':
		case '/progresstask@namabot':
		    if (isset($pisah[1])){
		        $idtask = $pisah[1];
		        $subtaskprogress = $pisah[2];
		        
		        include "koneksi-taskman.php";
		        $querysubtaskprogress = mysqli_query($conn, "CALL spupdatesubtaskprogress('$subtaskprogress', '$idtask');");
		        
		        $text = "тЬЕ _Progress subtask telah berhasil diupdate_ \n \n";
		        $text .= "Lihat list subtask: /ceksubtask $nama";
		        
    	    }else{
    	        $text = "тЪая╕П _Tidak bisa update progress subtask_";
    	        $text .= "\n \n Format update progress subtask: \n";
    	        $text .= "/subtaskprogress ,, [id sub-task] ,, [progress subtask] \n \n Contoh: \n /subtaskprogress ,, 6 ,, 75%";
    	    }
		    break;    
		    
		case '/taskstatus':
		case '/taskstatus@namabot':
		    if (isset($pisah[1])){
		        $idtask = $pisah[1];
		        $taskstatus = $pisah[2];
		        
		        if ($taskstatus == "Konsep") {
		            $updatetaskstatus = "20KONS";
		        } else if ($taskstatus == "Production") {
		            $updatetaskstatus = "30PROD";
		        } else if ($taskstatus == "Production IT") {
		            $updatetaskstatus = "31PRIT";
		        } else if ($taskstatus == "Production Design") {
		            $updatetaskstatus = "32PDES";
		        } else if ($taskstatus == "Production Konten") {
		            $updatetaskstatus = "33PKON";
		        } else if ($taskstatus == "Review") {
		            $updatetaskstatus = "40REVW";
		        } else if ($taskstatus == "Production Review") {
		            $updatetaskstatus = "41PROV";
		        } else if ($taskstatus == "Client Review") {
		            $updatetaskstatus = "42CLNV";
		        } else if ($taskstatus == "Done") {
		            $updatetaskstatus = "50DONE";
		        } else {
		            $updatetaskstatus = "10LIST";
		        }
		        
		        include "koneksi-taskman.php";
		        $querytambahtask = mysqli_query($conn, "CALL spupdatetaskstatus('$updatetaskstatus', '$idtask');");
		        
		        $text = "✅ _Status task telah berhasil diupdate_ \n \n";
		        $text .= "Lihat list task: /cektask $nama";
		        
    	    }else{
    	        $text = "⚠️ _Tidak bisa update status task_";
    	        $text .= "\n \n Format update status task: \n";
    	        $text .= "/taskstatus ,, [id task] ,, [status task] \n \n Contoh: \n /taskstatus ,, 3 ,, Production IT";
    	        $text .= "\n \n Status task: \n";
    	        $text .= "1. List Task \n";
    	        $text .= "2. Konsep \n";
    	        $text .= "3. Production \n";
    	        $text .= "3.1. Production IT \n";
    	        $text .= "3.1. Production Design \n";
    	        $text .= "3.2. Production Konten \n";
    	        $text .= "4. Review \n";
    	        $text .= "4.1. Production Review \n";
    	        $text .= "4.2. Client Review \n";
    	        $text .= "5. Done \n";
    	    }
		    break;
		    
		case '/subtaskstatus':
		case '/subtaskstatus@namabot':
		    if (isset($pisah[1])){
		        $idtask = $pisah[1];
		        $subtaskstatus = $pisah[2];
		        
		        if ($subtaskstatus == "Konsep") {
		            $updatetaskstatus = "20KONS";
		        } else if ($subtaskstatus == "Production") {
		            $updatetaskstatus = "30PROD";
		        } else if ($subtaskstatus == "Production IT") {
		            $updatetaskstatus = "31PRIT";
		        } else if ($subtaskstatus == "Production Design") {
		            $updatetaskstatus = "32PDES";
		        } else if ($subtaskstatus == "Production Konten") {
		            $updatetaskstatus = "33PKON";
		        } else if ($subtaskstatus == "Review") {
		            $updatetaskstatus = "40REVW";
		        } else if ($subtaskstatus == "Production Review") {
		            $updatetaskstatus = "41PROV";
		        } else if ($subtaskstatus == "Client Review") {
		            $updatetaskstatus = "42CLNV";
		        } else if ($subtaskstatus == "Done") {
		            $updatetaskstatus = "50DONE";
		        } else {
		            $updatetaskstatus = "10LIST";
		        }
		        
		        include "koneksi-taskman.php";
		        $querytambahtask = mysqli_query($conn, "CALL spupdatesubtaskstatus('$updatetaskstatus', '$idtask');");
		        
		        $text = "✅ _Status subtask telah berhasil diupdate_ \n \n";
		        $text .= "Lihat list task: /ceksubtask $nama";
		        
    	    }else{
    	        $text = "⚠️ _Tidak bisa update status subtask_";
    	        $text .= "\n \n Format update status task: \n";
    	        $text .= "/subtaskstatus ,, [id task] ,, [status subtask] \n \n Contoh: \n /subtaskstatus ,, 3 ,, Production IT";
    	        $text .= "\n \n Status task: \n";
    	        $text .= "1. List Task \n";
    	        $text .= "2. Konsep \n";
    	        $text .= "3. Production \n";
    	        $text .= "3.1. Production IT \n";
    	        $text .= "3.1. Production Design \n";
    	        $text .= "3.2. Production Konten \n";
    	        $text .= "4. Review \n";
    	        $text .= "4.1. Production Review \n";
    	        $text .= "4.2. Client Review \n";
    	        $text .= "5. Done \n";
    	    }
		    break;
		
		case '/progstart00':
		case '/progstart00@namabot':
		    if (isset($pisah[1])){
		        $namaproject = $pisah[1];
		        $task = $pisah[2];
		        $subtask = $pisah[3];
		        $statuspengerjaan = $pisah[4];
		        
		        date_default_timezone_set("Asia/Jakarta");
		        $tanggalsekarang = date('Y-m-d');
		        $jamsekarang = date('H:i:s');
		        
		        if ($namaproject == "DBC") {
		            $idproject = "01DK";
		        } else if ($namaproject == "HAOL") {
		            $idproject = "02HL";
		        } else if ($namaproject == "BA") {
		            $idproject = "03BA";
		        } else {
		            $idproject = "01DK";
		        }
		        
		        if ($statuspengerjaan == "Konsep") {
		            $updatestatus = "20KONS";
		        } else if ($statuspengerjaan == "Production") {
		            $updatestatus = "30PROD";
		        } else if ($statuspengerjaan == "Production IT") {
		            $updatestatus = "31PRIT";
		        } else if ($statuspengerjaan == "Production Design") {
		            $updatestatus = "32PDES";
		        } else if ($statuspengerjaan == "Production Konten") {
		            $updatestatus = "33PKON";
		        } else if ($statuspengerjaan == "Review") {
		            $updatestatus = "40REVW";
		        } else if ($statuspengerjaan == "Production Review") {
		            $updatestatus = "41PROV";
		        } else if ($statuspengerjaan == "Client Review") {
		            $updatestatus = "42CLNV";
		        } else if ($statuspengerjaan == "Done") {
		            $updatestatus = "50DONE";
		        } else {
		            $updatestatus = "10LIST";
		        }
		        
		        include "koneksi-taskman.php";
		        $querytambahtask = mysqli_query($conn, "CALL sptambahdailyprogstart('$tanggalsekarang', '$jamsekarang', '$iduser', '$idproject', '$task', '$subtask', '$updatestatus');");
		        $querylihatprogstart = mysqli_query($conn, "CALL splihatdailyprogstartbaruditambah;");
		        
		        $text = "Selamat mengerjakan! \n \n";
		        //$text .= "Task: \n";
		        while($data = mysqli_fetch_row($querylihatprogstart))
                {
                 $text .= "ID             : ";
                 $text .= $data[0];
                 $text .= " \n";
                 $text .= "Project   : ";
                 $text .= $data[5];
                 $text .= " \n";
                 $text .= "Task        : ";
                 $text .= $data[6];
                 $text .= " ($task) \n";
                 $text .= "Subtask : ";
                 $text .= $data[7];
                 $text .= " ($subtask) \n";
                 $text .= "Status    : ";
                 $text .= $data[8];
                 $text .= " ( ";
                 $text .= $data[9];
                 $text .= "% )";
                }
		        
    	    }else{
    	        $text = "⚠️ _Progstart Gagal_";
    	        $text .= "\n \n Format untuk progstart: \n";
    	        $text .= "/progstart ,, [Nama Project] ,, [Id Task] ,, [Id Subtask] ,, Status\n \n ";
    	        $text .= "Contoh: \n /progstart ,, HAOL ,, 4 ,, 10 ,, Production IT";
    	        $text .= "\n \n Nama Project: \n";
    	        $text .= "1. Dako BC : DBC \n";
    	        $text .= "2. Halaman Online : HAOL \n";
    	        $text .= "3. Business Area : BA \n \n";
    	        $text .= "Lihat daftar task: /cektask \n";
    	        $text .= "Lihat daftar task: /ceksubtask";
    	        $text .= "\n \n Status task: \n";
    	        $text .= "1. List Task \n";
    	        $text .= "2. Konsep \n";
    	        $text .= "3. Production \n";
    	        $text .= "3.1. Production IT \n";
    	        $text .= "3.1. Production Design \n";
    	        $text .= "3.2. Production Konten \n";
    	        $text .= "4. Review \n";
    	        $text .= "4.1. Production Review \n";
    	        $text .= "4.2. Client Review \n";
    	        $text .= "5. Done \n";
    	        
    	    }
		    
		    
		    break;
		    
		case '/progstart':
		case '/progstart@namabot':
		    if (isset($pisah[1])){
		        $idproject = $pisah[1];
		        $task = $pisah[2];
		        $subtask = $pisah[3];
		        $statuspengerjaan = $pisah[4];
		        
		        date_default_timezone_set("Asia/Jakarta");
		        $tanggalsekarang = date('Y-m-d');
		        $jamsekarang = date('H:i:s');
		        
		        if ($statuspengerjaan == "Konsep") {
		            $updatestatus = "20KONS";
		        } else if ($statuspengerjaan == "Production") {
		            $updatestatus = "30PROD";
		        } else if ($statuspengerjaan == "Production IT") {
		            $updatestatus = "31PRIT";
		        } else if ($statuspengerjaan == "Production Design") {
		            $updatestatus = "32PDES";
		        } else if ($statuspengerjaan == "Production Konten") {
		            $updatestatus = "33PKON";
		        } else if ($statuspengerjaan == "Review") {
		            $updatestatus = "40REVW";
		        } else if ($statuspengerjaan == "Production Review") {
		            $updatestatus = "41PROV";
		        } else if ($statuspengerjaan == "Client Review") {
		            $updatestatus = "42CLNV";
		        } else if ($statuspengerjaan == "Done") {
		            $updatestatus = "50DONE";
		        } else {
		            $updatestatus = "10LIST";
		        }
		        
		        include "koneksi-taskman.php";
		        $querytambahtask = mysqli_query($conn, "CALL sptambahdailyprogstart('$tanggalsekarang', '$jamsekarang', '$iduser', '$idproject', '$task', '$subtask', '$updatestatus');");
		        $querylihatprogstart = mysqli_query($conn, "CALL splihatdailyprogstartbaruditambah;");
		        
		        $text = "Selamat mengerjakan! \n \n";
		        //$text .= "Task: \n";
		        while($data = mysqli_fetch_row($querylihatprogstart))
                {
                 $text .= "ID             : ";
                 $text .= $data[0];
                 $text .= " \n";
                 $text .= "Project   : ";
                 $text .= $data[5];
                 $text .= " \n";
                 $text .= "Task        : ";
                 $text .= $data[6];
                 $text .= " ($task) \n";
                 $text .= "Subtask : ";
                 $text .= $data[7];
                 $text .= " ($subtask) \n";
                 $text .= "Status    : ";
                 $text .= $data[8];
                 $text .= " ( ";
                 $text .= $data[9];
                 $text .= "% )";
                }
		        
    	    }else{
    	        $text = "тЪая╕П _Progstart Gagal_";
    	        $text .= "\n \n Format untuk progstart: \n";
    	        $text .= "/progstart ,, [Id Project] ,, [Id Task] ,, [Id Subtask] ,, Status\n \n ";
    	        $text .= "Contoh: \n /progstart ,, 01DK ,, 4 ,, 10 ,, Production IT \n \n";
    	        $text .= "Lihat daftar project: /cekproject \n";
    	        $text .= "Lihat daftar task: /cektask \n";
    	        $text .= "Lihat daftar task: /ceksubtask";
    	        $text .= "\n \n Status task: \n";
    	        $text .= "1. List Task \n";
    	        $text .= "2. Konsep \n";
    	        $text .= "3. Production \n";
    	        $text .= "3.1. Production IT \n";
    	        $text .= "3.1. Production Design \n";
    	        $text .= "3.2. Production Konten \n";
    	        $text .= "4. Review \n";
    	        $text .= "4.1. Production Review \n";
    	        $text .= "4.2. Client Review \n";
    	        $text .= "5. Done \n";
    	        
    	    }
		    
		    
		    break;    
		    
		case '/progend':
		case '/progend@namabot':
		    if (isset($pecah2[1])){
		        $idprogstart = $pecah2[1];
		        
		        date_default_timezone_set("Asia/Jakarta");
		        $jamsekarang = date('H:i:s');
		        
		        include "koneksi-taskman.php";
		        $querytambahtask = mysqli_query($conn, "CALL spupdatetimestopdailyprogstart('$idprogstart', '$jamsekarang');");
		        
		        $text = "Thanks for Report! \n \n";
		        //$text .= "Lihat list task: /ceksubtask $nama";
		        
		    }else{
    	        $text = "⚠️ _Progend Gagal_";
    	        $text .= "\n \n Format untuk progend: \n";
    	        $text .= "/progend [id progstart]\n \n Contoh: \n /progend 3";
    	        $text .= "\n \n Lihat daftar progstart hari ini: \n /lihatprogstart";
    	    }
		    break;
		    
		case '/progend2':
		case '/progend@namabot':
		    if (isset($pisah[1])){
		        $idprogstart = $pisah[1];
		        $subtaskprogress = $pisah[2];
		        
		        date_default_timezone_set("Asia/Jakarta");
		        $jamsekarang = date('H:i:s');
		        
		        include "koneksi-taskman.php";
		        $queryupdateprogend = mysqli_query($conn, "CALL spupdatetimestopdailyprogstart('$idprogstart', '$jamsekarang');");
		        $querylihatidsubtask = mysqli_query($conn, "CALL splihatprogstartid('$idprogstart');");
		        
		        $data = mysqli_fetch_row($querylihatidsubtask);
		        $data[0] = $idsubtask;
		        $querysubtaskprogress = mysqli_query($conn, "CALL spupdatesubtaskprogress('$subtaskprogress', '$idsubtask');");
		        
		        $text = "Thanks for Report! \n \n";
		        //$text .= "$idprogstart";
		        //$text .= "\n";
		        //$text .= "$subtaskprogress";
		        //$text .= "\n";

		    }else{
    	        $text = "тЪая╕П _Progend Gagal_";
    	        $text .= "\n \n Format untuk progend: \n";
    	        $text .= "/progend2 ,, [id progstart] ,, [progress]\n \n Contoh: \n /progend ,, 3 ,, 75%";
    	        $text .= "\n \n Lihat daftar progstart hari ini: \n /lihatprogstart";
    	    }
		    break;
		  
        case '/time': 
		case '/time@namabot':
          date_default_timezone_set("Asia/Jakarta");
		  $waktusekarang = date('Y-m-d H:i:s');
          $text = "Waktu Sekarang: $waktusekarang\n";
		  //$text .= "Jadwal shalat: http://blogchem.com/shalat/widget.html";
          break;

        default:
          //$text .= "Klik /help untuk bantuan";
          break;
      }
        } else {
            //$text = 'Silahkan tulis pesan yang akan disampaikan..';
			//$text .= "\n";
			//$text .= "Format: /pesan `pesan`";
        }

        $hasil = sendMessage($idpesan, $idchat, $text);
        if ($GLOBALS['debug']) {
            // hanya nampak saat metode poll dan debug = true;
      echo 'Pesan yang dikirim: '.$text.PHP_EOL;
            print_r($hasil);
        }
    }
}

// pencetakan versi dan info waktu server, berfungsi jika test hook
echo 'Ver. '.myVERSI.' OK Start!'.PHP_EOL.date('d-m-Y H:i:s').PHP_EOL;

function printUpdates($result)
{
    foreach ($result as $obj) {
        // echo $obj['message']['text'].PHP_EOL;
    processMessage($obj);
        $last_id = $obj['update_id'];
    }

    return $last_id;
}


// AKTIFKAN INI jika menggunakan metode poll
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
/*
$last_id = null;
while (true) {
    $result = getUpdates($last_id);
    if (!empty($result)) {
        echo '+';
        $last_id = printUpdates($result);
    } else {
        echo '-';
    }

    sleep(1);
}
*/
// AKTIFKAN INI jika menggunakan metode webhook
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
  exit;
} else {
  processMessage($update);
}

?>