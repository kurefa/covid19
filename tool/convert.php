<?php
require __DIR__.'/vendor/autoload.php';
use Spatie\PdfToText\Pdf;
use Carbon\Carbon;
use Goutte\Client;

function getAttachmentFileInfo($target_url) : array
{
    $client = new Client();
    $crawler = $client->request('GET', $target_url);
    $values = $crawler->filter('#danraku1 a')->reduce(function($element) {
        if (! preg_match('/日別検査状況/u', $element->text(), $matches)) return false;
    })->each(function($element) {
        preg_match('/日別検査状況（(.+)(\d{1,2})年(\d{1,2})月(\d{1,2})日(\d{1,2})時(\d{1,2})分.+）/u', $element->text(), $matches);

        // 適当な和暦パーサーがないので固定
        if ($matches[1] == '令和') $matches[2] += 2018;
        $page_date = Carbon::parse($matches[2] . '-' . $matches[3] . '-' . $matches[4] . ' ' . $matches[5] . ':' . $matches[6]);

        return [
            'date' => $page_date,
            'url' => $element->link()->getUri(),
        ];
    });

    return $values[0];
}

function getPatients($target_url) : array
{
    $client = new Client();
    $crawler = $client->request('GET', $target_url);
    $values = $crawler->filter('#danraku1 div a')->reduce(function($element) {
        if (! preg_match('/詳細はこちら/u', $element->text(), $matches)) return false;
        if (! preg_match('/例目：/u', $element->parents()->text(), $matches)) return false;
    })->each(function($element) {
        preg_match('/(\d+)例目：(.+代)\s*(男性|女性)\s*(.+)在住/u', $element->parents()->text(), $matches);
        return [
            'no' => $matches[1],
            'age' => $matches[2],
            'sex' => $matches[3],
            'living' => $matches[4],
        ];
    });

    return $values;
}

function downloadFile($target_url, $save_file)
{
    if (($data = file_get_contents($target_url)) !== false) {
        if (file_put_contents($save_file, $data) !== false) {
            echo "pdf file download success. --> $save_file\n";
            return;
        } else {
            throw Exception("file not found:$target_url");
        }
    }
}

function extractWordFromPdf($target_file, $publish_date) : array
{
    // 既存data.json読み込み
    $data = file_get_contents(dirname(__FILE__) . '/../data/data.json');
    $json = json_decode($data, true);

    // PDF読み込み
    $text = (new Pdf())
    ->setPdf($target_file)
    ->setOptions(['layout', 'eol unix'])
    ->text();

    $lines = explode("\n", $text);

    $date_json_format = $publish_date->format('Y/m/d H:i');

    $json['inspections_summary']['data'] = [];
    $json['inspections_summary']['labels'] = [];

    foreach ($lines as $line) {
        // PDFの3/11に含まれる0x0cを除去
        $line = trim($line, "\x0c");

        $fields = preg_split("/\s+/", $line);
        if (!is_numeric($fields[0])) continue;

        // 年情報がないので、2020年固定で処理する
        $date = '2020-' . sprintf('%02d-%02d', $fields[0], $fields[1]) . 'T08:00:00.000Z';
        $pdf_date = Carbon::parse($date);

        foreach (['patients_summary', 'discharges_summary'] as $section) {
            $is_change = false;
            $last_file_date = null;

            $json[$section]['date'] = $date_json_format;
            foreach ($json[$section]['data'] as &$data) {
                // 退院者数は取得できないので既存データを維持
                $count = ($section == 'patients_summary') ? $fields[4] : $data['小計'];

                $data_date = Carbon::parse($data['日付']);
                $last_file_date = $data_date;

                // 既存データ置換
                if ($pdf_date->isSameDay($data_date)) {
                    $data['小計'] = (int)$count;
                    $is_change = true;
                }
            }

            // 追加
            if ($is_change == false and is_null($last_file_date) == false and $last_file_date->lte($pdf_date)) {
                // 退院者数は取得できないので0をセット
                $count = ($section == 'patients_summary') ? $fields[4] : 0;

                $json[$section]['data'][] = [
                    '日付' => $pdf_date->format('Y-m-d') . 'T08:00:00.000Z',
                    '小計' => (int)$count,
                ];
            }
        }

        $json['inspections_summary']['data']['佐賀県内'][] = (int)$fields[3];
        $json['inspections_summary']['data']['その他'][] = 0;
        $json['inspections_summary']['labels'][] = $pdf_date->format('n/j');
    }

    $json['contacts']['date'] = $date_json_format;
    $json['querents']['date'] = $date_json_format;
    $json['patients']['date'] = $date_json_format;
    $json['inspections_summary']['date'] = $date_json_format;
    $json['inspections']['date'] = $date_json_format;
    $json['lastUpdate'] = $date_json_format;

    return $json;
}

$target_url = 'https://www.pref.saga.lg.jp/kiji00373220/index.html';
$save_file = dirname(__FILE__) . '/downloads/download_file.pdf';

$attachment_file = getAttachmentFileInfo($target_url);
downloadFile($attachment_file['url'], $save_file);

$json = extractWordFromPdf($save_file, $attachment_file['date']);
$patients = getPatients($target_url);

// 患者情報に日付がないので、件数で比較
if (count($patients) > count($json['patients']['data'])) {
    for ($i = count($json['patients']['data']); $i < count($patients); $i++) {
        $json['patients']['data'][] = [
            'リリース日' => Carbon::now()->format('Y-m-d') . 'T08:00:00.000Z', // 日付情報がないので今日をセット
            '居住地' => '佐賀県' . $patients[$i]['living'],
            '年代' => $patients[$i]['age'],
            '性別' => $patients[$i]['sex'],
            '退院' => null, // 退院情報がないのでnullをセット
            'date' => Carbon::now()->format('Y-m-d'),
        ];
    }
}

// 集計値更新
// 検査数
$sum_inspections_count = 0;
foreach ($json['inspections_summary']['data']['佐賀県内'] as $data) {
    $sum_inspections_count += $data;
}
$json['main_summary']['value'] = $sum_inspections_count;

// 陽性患者数
$sum_patients_count = 0;
foreach ($json['patients_summary']['data'] as $data) {
    $sum_patients_count += $data['小計'];
}
$json['main_summary']['children'][0]['value'] = $sum_patients_count;

// 退院者数
$sum_discharges_count = 0;
foreach ($json['discharges_summary']['data'] as $data) {
    $sum_discharges_count += $data['小計'];
}
$json['main_summary']['children'][0]['children'][1]['value'] = $sum_discharges_count;
$json['main_summary']['children'][0]['children'][0]['value'] = $sum_patients_count - $sum_discharges_count;

$wh = fopen(dirname(__FILE__) . '/data.json', 'w');
fwrite($wh, json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
fclose($wh);
echo "generated ./data.json\n";
