<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\City;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['code'=>'11','name'=>'Aceh','cities'=>['Banda Aceh','Sabang','Langsa','Lhokseumawe','Subulussalam']],
            ['code'=>'12','name'=>'Sumatera Utara','cities'=>['Medan','Binjai','Pematang Siantar','Tebing Tinggi','Tanjung Balai','Sibolga','Padang Sidempuan','Gunungsitoli']],
            ['code'=>'13','name'=>'Sumatera Barat','cities'=>['Padang','Bukittinggi','Payakumbuh','Padang Panjang','Solok','Sawahlunto','Pariaman']],
            ['code'=>'14','name'=>'Riau','cities'=>['Pekanbaru','Dumai']],
            ['code'=>'15','name'=>'Jambi','cities'=>['Jambi','Sungai Penuh']],
            ['code'=>'16','name'=>'Sumatera Selatan','cities'=>['Palembang','Prabumulih','Pagar Alam','Lubuklinggau']],
            ['code'=>'17','name'=>'Bengkulu','cities'=>['Bengkulu']],
            ['code'=>'18','name'=>'Lampung','cities'=>['Bandar Lampung','Metro']],
            ['code'=>'19','name'=>'Kep. Bangka Belitung','cities'=>['Pangkalpinang']],
            ['code'=>'21','name'=>'Kep. Riau','cities'=>['Batam','Tanjungpinang']],
            ['code'=>'31','name'=>'DKI Jakarta','cities'=>['Jakarta Pusat','Jakarta Utara','Jakarta Barat','Jakarta Selatan','Jakarta Timur','Kepulauan Seribu']],
            ['code'=>'32','name'=>'Jawa Barat','cities'=>['Bandung','Bekasi','Bogor','Cimahi','Cirebon','Depok','Sukabumi','Tasikmalaya','Banjar']],
            ['code'=>'33','name'=>'Jawa Tengah','cities'=>['Semarang','Surakarta','Salatiga','Magelang','Pekalongan','Tegal']],
            ['code'=>'34','name'=>'DI Yogyakarta','cities'=>['Yogyakarta']],
            ['code'=>'35','name'=>'Jawa Timur','cities'=>['Surabaya','Malang','Mojokerto','Pasuruan','Batu','Blitar','Kediri','Madiun','Probolinggo']],
            ['code'=>'36','name'=>'Banten','cities'=>['Serang','Cilegon','Tangerang','Tangerang Selatan']],
            ['code'=>'51','name'=>'Bali','cities'=>['Denpasar']],
            ['code'=>'52','name'=>'Nusa Tenggara Barat','cities'=>['Mataram','Bima']],
            ['code'=>'53','name'=>'Nusa Tenggara Timur','cities'=>['Kupang']],
            ['code'=>'61','name'=>'Kalimantan Barat','cities'=>['Pontianak','Singkawang']],
            ['code'=>'62','name'=>'Kalimantan Tengah','cities'=>['Palangka Raya']],
            ['code'=>'63','name'=>'Kalimantan Selatan','cities'=>['Banjarmasin','Banjarbaru']],
            ['code'=>'64','name'=>'Kalimantan Timur','cities'=>['Samarinda','Balikpapan','Bontang']],
            ['code'=>'65','name'=>'Kalimantan Utara','cities'=>['Tarakan']],
            ['code'=>'71','name'=>'Sulawesi Utara','cities'=>['Manado','Bitung','Tomohon','Kotamobagu']],
            ['code'=>'72','name'=>'Sulawesi Tengah','cities'=>['Palu']],
            ['code'=>'73','name'=>'Sulawesi Selatan','cities'=>['Makassar','Palopo','Parepare']],
            ['code'=>'74','name'=>'Sulawesi Tenggara','cities'=>['Kendari','Bau-Bau']],
            ['code'=>'75','name'=>'Gorontalo','cities'=>['Gorontalo']],
            ['code'=>'76','name'=>'Sulawesi Barat','cities'=>['Mamuju']],
            ['code'=>'81','name'=>'Maluku','cities'=>['Ambon','Tual']],
            ['code'=>'82','name'=>'Maluku Utara','cities'=>['Ternate','Tidore Kepulauan']],
            ['code'=>'91','name'=>'Papua Barat','cities'=>['Sorong','Manokwari']],
            ['code'=>'94','name'=>'Papua','cities'=>['Jayapura']],
        ];

        foreach ($data as $prov) {
            $province = Province::firstOrCreate(['code' => $prov['code']], ['name' => $prov['name']]);
            foreach ($prov['cities'] as $i => $cityName) {
                City::firstOrCreate(
                    ['code' => $prov['code'] . str_pad($i + 1, 2, '0', STR_PAD_LEFT)],
                    ['province_id' => $province->id, 'name' => $cityName]
                );
            }
        }
    }
}
