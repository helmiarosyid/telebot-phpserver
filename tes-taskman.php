<?php
include "koneksi-taskman.php";
              //$text = "Daftar task kak $carinama :\n \n";
              $tampilquery = mysqli_query("call spcaritaskbynama('Helmi');");
              if(mysqli_num_rows($tampilquery)>0)
              {
                  $no = 1;
                  while($data = mysqli_fetch_array($tampilquery))
                  {
                    $text .= $no;
                    $text .= "\n";
                    $text .= $data['Task'];
                    //$text .= "\n";
                    //$text .= $data['Sub-Task'];
                    //$text .= "\n";
                    $text .= $data['Progress'];
                    //$text .= "\n";
                    //$text .= $data['Status'];
                    $text .= "\n";
                    $no++;
                  }
               }
?>