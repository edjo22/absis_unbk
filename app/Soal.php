<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Soal //extends Model
{	
	private $database = 'soal';
    private $insert_status = true;
    public $kode_to;
    public $id_mapel;
    public $kode_kd;
    public $kode_soal;
    public $isi_soal;

    public $status_acak;
    public $no_soal;
    public $tingkat_kesulitan;
    public $tipe_soal;
    public $kode_master;

    public $jawaban;
    public $error;

    public function __construct($kode_to,$id_mapel,$id_soal=null){

        if (($kode_to<>null) && ($id_mapel<>null)) {
            
            if ($this->cekKodeSoal($id_soal) && ($id_soal != null)) {

                //kalo soalnya ada
                
                $this->insert_status = false;

            } else {

                //kalo ngga ada soal

                $this->kode_to = $kode_to;
                $this->id_mapel = $id_mapel;

            }

        } else {

           // break;

        }

    		
    }

    public function listAll(){

        $soal = DB::table($this->database)
                    ->where('kode_to',$this->kode_to)
                    ->where('id_mapel',$this->id_mapel)
                    ->get();

        return $soal;

    }

    public function first(){

        $soal = DB::table($this->database)
                    ->where('kode_to',$this->kode_to)
                    ->where('id_mapel',$this->id_mapel)
                    ->first();

        return $soal;

    }

    public function save(){

        if ($this->insert_status) {

            $this->insert();

        } else {

            $this->update();

        }

    }

    private function insert(){

        $id1 = DB::table($this->database)->insertGetId(
            ['id_mapel'=> $this->id_mapel, 
             'kode_kd' => $this->kode_kd,
             'kode_to'=> $this->kode_to,
             'kode_soal' => 'alaiho gambreng',
             'isi_soal'=> $this->isi_soal]
        );

        $kode_soal = sha1('soal'.$id1);

        $id2 = DB::table($this->database)
                ->where('id',$id1)
                ->update(['kode_soal'=> $kode_soal]);

        $this->buatJawabanDefault($kode_soal);

        if (isset($id1) && isset($id2)){
            return true;
        }

    }

    private function update(){

        $jo1= DB::table($this->database)
            ->where('kode_soal', $this->kode_soal)
            ->update(['kode_kd' => $this->kode_kd,
                     'isi_soal'=> $this->isi_soal]);

            if(isset($jo1))
            {
                return true;

            } else {

                return false;

            }

    }

    public function delete(){

        if ($this->insert_status == false ) {

            DB::table($this->database)->where('kode_soal', $this->kode_soal)->delete();

        }

    }

    public function cekKodeSoal($kode_soal){

        $soal = DB::table($this->database)
                ->where('kode_soal',$kode_soal)
                ->first();

        if (isset($soal->id)) {

            $this->id_mapel = $soal->id_mapel;
            $this->kode_kd = $soal->kode_kd;
            $this->kode_to =  $soal->kode_to;
            $this->kode_soal = $soal ->kode_soal;
            $this->isi_soal = $soal->isi_soal;

            $this->status_acak= $soal->status_acak;  
            $this->no_soal= $soal->no_soal;  
            $this->tingkat_kesulitan= $soal->tingkat_kesulitan;  
            $this->tipe_soal= $soal->tipe_soal;  
            $this->kode_master= $soal->kode_master;


            return true;

        } else {

            return false;
        }

    }

    private function buatJawabanDefault($kode_soal){
        for ($i=0; $i < 5; $i++) { 

            if ($i==0) {
                $status_benar=1;
            } else{
                $status_benar=0;
            }

            $id1 = DB::table('soal_jawaban')->insertGetId(
                ['kode_soal'=> $kode_soal, 
                 'kode_jawaban' => sha1($kode_soal.$i),
                 'status_benar'=> $status_benar,
                 'isi_jawaban' => '',
                 'urutan_untuk_guru' => $i]
              );
        }
    }


    public function updateIsiJawaban($kode_jawaban,$isi_jawaban){

        $jo1= DB::table('soal_jawaban')
            ->where('kode_jawaban', $kode_jawaban)
            ->update(['isi_jawaban' => $isi_jawaban]);

    }

    public function updateJawabanBenar($kode_jawaban){

        if($this->kode_soal<>null){

        //bikin semua jawaban salah
        $jo1= DB::table('soal_jawaban')
            ->where('kode_soal', $this->kode_soal)
            ->update(['status_benar' => 0]);

        //bikin jawaban yg dikirim jadi jawaban yg bener
        $jo1= DB::table('soal_jawaban')
            ->where('kode_jawaban', $kode_jawaban)
            ->update(['status_benar' => 1]);
        

        }

    }

    public function updateStatusAcak($kode_soal,$status_acak){

        if($this->kode_soal<>null){

        //bikin semua jawaban salah

            $jo1= DB::table('soal')
                ->where('kode_soal', $kode_soal)
                ->update(['status_acak' => $status_acak]);

            

            if ($status_acak==1) {
                
                $jo1= DB::table('soal')
                    ->where('kode_soal', $kode_soal)
                    ->update(['no_soal' => 0,
                              'status_acak' => 1]);

                return 0;

            } elseif ($status_acak==0) {

                //get no soal tidak acak terbesar
                // $soal = DB::table('soal')
                //         ->where('kode_to',$this->kode_to)
                //         ->where('id_mapel',$this->id_mapel)
                //         ->where('status_acak',"0")
                //         ->orderBy('no_soal', 'desc')
                //         ->first();

                // $no_soal_terbesar = $soal->no_soal;

                // if ($no_soal_terbesar==0) {

                //     $no_soal = 1;

                // } elseif (($no_soal_terbesar<>0) AND ($no_soal_terbesar>0)){

                //     $no_soal = $no_soal_terbesar + 1;

                // }
                
                // $jo1= DB::table('soal')
                //     ->where('kode_soal', $kode_soal)
                //     ->update(['no_soal' => $no_soal]);
                $no_soal=0;

                return $no_soal;

            }
        }

    }

    public function updateNoSoal($kode_soal,$no_soal){

        if($this->kode_soal<>null){

            //ngecek dia soal acak atau ngga
            $soal = DB::table('soal')
                        ->where('kode_soal',$kode_soal)
                        ->first();

            if ($soal->status_acak==0) {

                $soal = DB::table('soal')
                        ->where('kode_to',$this->kode_to)
                        ->where('id_mapel',$this->id_mapel)
                        ->where('no_soal',$no_soal)
                        ->first();

                //ngecek udah ada blom no soal yg sama
                if (isset($soal->no_soal) AND ($soal->kode_soal<>$kode_soal)) {

                    $this->error = "sudah ada no soal yg sama";

                    return false;
                    # code...
                } else {

                    $jo1= DB::table('soal')
                    ->where('kode_soal', $kode_soal)
                    ->update(['no_soal' => $no_soal]);

                return true;

                }

                

            } else {

                $this->error ="status soal diacak, tidak dapat mengisi no soal";
                return false;

            }

        }

    }

    public function updateKD($kode_soal,$kode_kd){

        $jo1= DB::table('soal')
        ->where('kode_soal', $kode_soal)
        ->update(['kode_kd' => $kode_kd]);

    }

    public function updateTingkatKesulitan($kode_soal,$tingkat_kesulitan){

        $jo1= DB::table('soal')
        ->where('kode_soal', $kode_soal)
        ->update(['tingkat_kesulitan' => $tingkat_kesulitan]);

    }

    public function getJawaban(){

        $soal = DB::table('soal_jawaban')
                    ->where('kode_soal',$this->kode_soal)
                    ->get();

        return $soal;

    }

    public function getIsiSoal(){

        $soal = DB::table('soal')
                    ->where('kode_soal',$this->kode_soal)
                    ->first();

        return $soal;

    }

    public function createAcakSoal($kode_to_auth){

            $soal_base_non_acak = DB::table($this->database)
                                ->where('kode_to',$this->kode_to)
                                ->where('id_mapel',$this->id_mapel)
                                ->where('status_acak',0)
                                ->get();

            if (isset($soal_base_non_acak[0])) {
                $jumlah_soal_base_non_acak = sizeof($soal_base_non_acak);
            } else {
                $jumlah_soal_base_non_acak=0;
            }

            

            $soal_base = DB::table($this->database)
                        ->where('kode_to',$this->kode_to)
                        ->where('id_mapel',$this->id_mapel)
                        ->where('status_acak',1)
                        ->get();

            $jumlah_soal_base = sizeof($soal_base);

            $total_soal = $jumlah_soal_base_non_acak + $jumlah_soal_base;

            $soal_siswa = DB::table('jawaban_siswa')
                    ->where('kode_to_auth',$kode_to_auth)
                    ->get();

            $jumlah_soal_siswa = sizeof($soal_siswa);

            
            //bumi hanguskan key yg udah digenerate
            if (($jumlah_soal_siswa>0) AND ($jumlah_soal_siswa<>$total_soal)){

                DB::table('jawaban_siswa')->where('kode_to_auth', $kode_to_auth)->delete();

                 $soal_base = DB::table($this->database)
                        ->where('kode_to',$this->kode_to)
                        ->where('id_mapel',$this->id_mapel)
                        ->get();

                $jumlah_soal_base = sizeof($soal_base);

                $soal_siswa = DB::table('jawaban_siswa')
                        ->where('kode_to_auth',$kode_to_auth)
                        ->get();

                $jumlah_soal_siswa = sizeof($soal_siswa);

            }
            

            if ($jumlah_soal_siswa==$total_soal) {



                return true;

            } elseif (($jumlah_soal_siswa>0) AND ($jumlah_soal_siswa != $total_soal)){

                ///INI PERINTAH KALAU SOAL DI SISWA DAN DI DATBASE NGGA COCOK, misal hapus generate key, malah guru edit soal-> nambah soal
                //solusi sementara, key akan dihanguskan, code di atas sono

                // DB::table('jawaban_siswa')->where('kode_to_auth', $kode_to_auth)->delete();
                // $this->createAcakSoal($kode_to_auth);

                    // $list_kode_soal_siswa = array();

                    // for ($i=0; $i < $jumlah_soal_siswa; $i++) { 
                    //     array_push($list_kode_soal_siswa,$soal_siswa[$i]->kode_soal);
                    // }

                    // $soal_base_kurang = DB::table($this->database)
                    //         ->where('kode_to',$this->kode_to)
                    //         ->where('id_mapel',$this->id_mapel)
                    //         ->whereNotIn('kode_soal',$list_kode_soal_siswa)
                    //         ->get();

                    // $jumlah_soal_base_kurang = sizeof($soal_base);

                    // //$this->error = var_dump($list_kode_soal_siswa);

                    // $array_soal =array();

                    // $cucok = false;

                    // $batas_acak = $jumlah_soal_base_kurang-1;


                    // $x=0;

                    // while ($x<$jumlah_soal_base_kurang) {

                    //     $angka = mt_rand(0,$batas_acak);

                    //     if ($this->cari_array($array_soal,$angka) == false ) {
                            
                    //         array_push($array_soal, $angka);

                    //         $x++;

                    //     } else {

                    //         continue;
                    //     }
                        
                    // }

                    // for ($i=0; $i < $jumlah_soal_base_kurang; $i++) { 

                    //     $no_soal = $soal_siswa[$jumlah_soal_siswa-1]->no_soal + $i + 1;//$jumlah_soal_siswa - 1;

                    //     $kode_jawaban_siswa = hash('sha256',$kode_to_auth.$no_soal);

                    //     $kode_soal = $soal_base[$array_soal[$i]]->kode_soal;

                    //     $id1 = DB::table('jawaban_siswa')->insert(
                    //         ['kode_to_auth'=> $kode_to_auth, 
                    //          'kode_jawaban_siswa' => $kode_jawaban_siswa,
                    //          'no_soal'=> $i+1,
                    //          'kode_soal' => $kode_soal]);
                    // }

                    // $soal_siswa = DB::table('jawaban_siswa')
                    //         ->where('kode_to_auth',$kode_to_auth)
                    //         ->get();

                    // $jumlah_soal_siswa = sizeof($soal_siswa);

                    // if ($jumlah_soal_siswa==$jumlah_soal_base_kurang) {

                    //     return true;

                    //     //$this->error = var_dump($array_soal);//$jumlah_soal_base;

                    // } else {

                    //     $this->error = var_dump($soal_base_kurang);//$jumlah_soal_base;

                    //     return false;
                    // }
                    $this->error = "jumlah soal base dan siswa tidak sama ";
                    
                    return false;

                

            } else {

                for ($i=0; $i < $jumlah_soal_base_non_acak; $i++) { 

                    $no_soal = $i + 1;

                    $kode_jawaban_siswa = hash('sha256',$kode_to_auth.$no_soal);

                    $kode_soal = $soal_base_non_acak[$i]->kode_soal;

                    $id1 = DB::table('jawaban_siswa')->insert(
                        ['kode_to_auth'=> $kode_to_auth, 
                         'kode_jawaban_siswa' => $kode_jawaban_siswa,
                         'no_soal'=> $soal_base_non_acak[$i]->no_soal,
                         'kode_soal' => $kode_soal]);
                }

                

                $array_soal =array();

                $cucok = false;

                $batas_bawah = $jumlah_soal_base_non_acak;

                $batas_acak = $jumlah_soal_base-1;


                $x=0;

                while ($x<$jumlah_soal_base) {

                    $angka = mt_rand(0,$batas_acak);

                    if ($this->cari_array($array_soal,$angka) == false ) {
                        
                        array_push($array_soal, $angka);

                        $x++;

                    } else {

                        continue;
                    }
                    
                }

                for ($i=0; $i < $jumlah_soal_base; $i++) { 

                    $no_soal = $i+1+$jumlah_soal_base_non_acak;

                    $kode_jawaban_siswa = hash('sha256',$kode_to_auth.$no_soal);

                    $kode_soal = $soal_base[$array_soal[$i]]->kode_soal;

                    $id1 = DB::table('jawaban_siswa')->insert(
                        ['kode_to_auth'=> $kode_to_auth, 
                         'kode_jawaban_siswa' => $kode_jawaban_siswa,
                         'no_soal'=> $i+1+$jumlah_soal_base_non_acak,
                         'kode_soal' => $kode_soal]);
                }

                $soal_siswa = DB::table('jawaban_siswa')
                        ->where('kode_to_auth',$kode_to_auth)
                        ->get();

                $jumlah_soal_siswa = sizeof($soal_siswa);

               if ($jumlah_soal_siswa==$total_soal) {

                    
                    //$this->error = var_dump($array_soal);//$jumlah_soal_base;

                    return true;

                } else {

                    $this->error = var_dump($array_soal);//$jumlah_soal_base;

                    return false;
                }

            }


            
        }

        public function cari_array($array,$key){

            for ($i=0; $i < sizeof($array); $i++) { 

                if ($array[$i]==$key) {

                    return true;

                } else {

                    if ($i==sizeof($array)) {

                        return false;

                    }

                }
            }

        }


}




