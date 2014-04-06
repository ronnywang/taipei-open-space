<?php

class Getter
{
    public function getData($use_no)
    {
        list($year, $no) = explode('-', $use_no);
        $no = sprintf("%04d", $no % 10000);
        $url = "https://raw.githubusercontent.com/ronnywang/cpabm.cpami.gov.tw/master/outputs/G00/{$year}/{$year}{$no}.json";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Taiwan Open Space finder');
        $ret = curl_exec($curl);
        $content_json = json_decode(iconv('Big5', 'UTF-8', $ret));
        if (!$content_json) {
            throw new Exception("{$use_no} 不是合法的 JSON");
        }

        $data = $content_json->licenseData;
        foreach ($data as $license_data) {
            if ($license_data->licenseStr_1 == "{$year}使字第{$no}號") {
                return $license_data;
            }
        }
        throw new Exception("{$use_no} 找不到");
    }

    public function main()
    {
        $fp = fopen('list.csv', 'r');
        $columns = fgetcsv($fp);
        $ret = array();
        while($row = fgetcsv($fp)) {
            list($no, $use_no, $build_no, $address, $manager) = $row;
            try {
                $data = $this->getData($use_no);
            } catch (Exception $e) {
                error_log($e->getMessage());
                continue;
            }
            $ret[] = array(
                '編號' => $no,
                '使照號碼' => $use_no,
                '建照號碼' => $build_no,
                '地址' => $address,
                '管理委員會' => $manager,
                '使照內容' => $data,
            );
        }

        file_put_contents('output.json', json_encode($ret, JSON_UNESCAPED_UNICODE));

    }
}

$g = new Getter;
$g->main();
