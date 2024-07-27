<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
$apiKey = "";
// Load the spreadsheet
$spreadsheet = IOFactory::load('A1.xlsx');
$sheet = $spreadsheet->getSheet(0);
$dataXlsx = $sheet->toArray();
// Function to search for a value in column A and return values from columns B and D
function searchData($data, $searchValue) {
    foreach ($data as $row) {
        if (isset($row[0]) && $row[0] == $searchValue) { // Column A
            return [
                'valueB' => $row[1], // Column B
                'valueD' => $row[3]  // Column D
            ];
        }
    }
    return null;
}

// Get the raw POST data
$data = file_get_contents("php://input");
$update = json_decode($data, true);

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
   $text = $update['message']['text'];

    // Search for the name in the Excel file
    $result = searchData($dataXlsx, $text);

    if ($result) {
        $reply = "الدرجة: " . $result['valueB'] . "\nالمحافظة: " . $result['valueD'];
    } else {
        $reply = "عذرا الاسم غير متواجد";
    }

    // Send the reply back to Telegram
    $url = "https://api.telegram.org/bot$apiKey/sendMessage";
    $options = array(
        'http' => array(
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode(array(
                'chat_id' => $chat_id,
                'text'    => $reply
            )),
        ),
    );
    $context  = stream_context_create($options);
    file_get_contents($url, false, $context);
}
?>
