<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\Loader::includeModule('iblock');

$elements = \Bitrix\Iblock\Elements\ElementObjectsTable::getList([
    'select' => ['ID', 'NAME', 'CITY', 'SUMM', 'COORDS'],
    'filter' => ['=ACTIVE' => 'Y', 'IBLOCK_ID' => 254, 'IBLOCK_ELEMENTS_ELEMENT_OBJECTS_CITY_VALUE' => 'Уфа'],
//    'limit' => 50,
])->fetchAll();

/**/
$data = '{
  "type": "FeatureCollection",
  "features": [';

foreach ($elements as $key => $element) {

    if($element['IBLOCK_ELEMENTS_ELEMENT_OBJECTS_SUMM_VALUE'] <= 1000000){
        $pointPreset = 'islands#darkGreenDotIconWithCaption';
        $clusterCaption = "менее 1 млн руб";
        $hintContent = "менее 1 млн руб";
        $iconCaption = "менее 1 млн руб";
    }
    if(($element['IBLOCK_ELEMENTS_ELEMENT_OBJECTS_SUMM_VALUE'] < 3000000) && ($element['IBLOCK_ELEMENTS_ELEMENT_OBJECTS_SUMM_VALUE'] > 1000000)){
        $pointPreset = 'islands#yellowDotIconWithCaption';
        $clusterCaption = "от 1 до 3 млн руб";
        $hintContent = "от 1 до 3 млн руб";
        $iconCaption = "от 1 до 3 млн руб";
    }

    if(($element['IBLOCK_ELEMENTS_ELEMENT_OBJECTS_SUMM_VALUE'] < 5000000) && ($element['IBLOCK_ELEMENTS_ELEMENT_OBJECTS_SUMM_VALUE'] > 3000000)){
        $pointPreset = 'islands#orangeDotIconWithCaption';
        $clusterCaption = "от 3 до 5 млн руб";
        $hintContent = "от 3 до 5 млн руб";
        $iconCaption = "от 3 до 5 млн руб";
    }

    if($element['IBLOCK_ELEMENTS_ELEMENT_OBJECTS_SUMM_VALUE'] > 5000000){
        $pointPreset = 'islands#redDotIconWithCaption';
        $clusterCaption = "от 5 млн руб и выше";
        $hintContent = "от 5 млн руб и выше";
        $iconCaption = "от 5 млн руб и выше";
    }

    $numberDot = str_replace(',', '.', $element['IBLOCK_ELEMENTS_ELEMENT_OBJECTS_SUMM_VALUE']); // замена запятой на точку
    $strToNumber = floatval($numberDot); // меняем тип данных на число с плавающей точкой
    $priceFormat = number_format($strToNumber, 2, ',', ' ') . ' руб';

    if ($key === array_key_last($elements)) {
        $data .= '{
          "type": "Feature",
          "id": ' . $element['ID'] . ',
          "geometry": {
            "type": "Point",
            "coordinates": [
              ' . $element['IBLOCK_ELEMENTS_ELEMENT_OBJECTS_COORDS_VALUE'] . '
            ]
          },
          "properties": {
            "balloonContent": "<div class=\'balloonHeader\'>Общее:</div><span class=\'balloonTitle\'>Адрес: </span><b>' . $element['NAME'] . '</b><br><br><span class=\'balloonTitle\'>Задолженность: </span><b>' . $priceFormat . '</b>",
            "clusterCaption": "'.$clusterCaption.'",
            "hintContent": "'.$hintContent.'",
            "iconCaption": "'.$iconCaption.'"
          },
          "options": {
            "preset": "'.$pointPreset.'"
          }
        }
    ';
    } else {
        $data .= '{
          "type": "Feature",
          "id": ' . $element['ID'] . ',
          "geometry": {
            "type": "Point",
            "coordinates": [
              ' . $element['IBLOCK_ELEMENTS_ELEMENT_OBJECTS_COORDS_VALUE'] . '
            ]
          },
          "properties": {
            "balloonContent": "<div class=\'balloonHeader\'>Общее:</div><span class=\'balloonTitle\'>Адрес: </span><b>' . $element['NAME'] . '</b><br><br><span class=\'balloonTitle\'>Задолженность: </span><b>' . $priceFormat . '</b>",
            "clusterCaption": "'.$clusterCaption.'",
            "hintContent": "'.$hintContent.'",
            "iconCaption": "'.$iconCaption.'"
          },
          "options": {
            "preset": "'.$pointPreset.'"
          }
        },
        ';
    }
}
\
$data .= '  ]
}
';
header("Content-Type: application/json");
echo $data;
exit();
?>