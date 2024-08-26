<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('catalog');


/*$address = 'г Уфа, ул. Свободы, д.45';
$coords = getCoordinates($address);
print_r($coords);*/

\Bitrix\Main\Loader::includeModule('iblock');

$elements = \Bitrix\Iblock\Elements\ElementObjectsTable::getList([
    'select' => ['ID', 'NAME', 'CITY', 'SUMM', 'COORDS'],
    'filter' => ['=ACTIVE' => 'Y', 'IBLOCK_ID' => 254, 'IBLOCK_ELEMENTS_ELEMENT_OBJECTS_CITY_VALUE' => 'Уфа', 'ID' => 1968539],
    'limit' => 1,
])->fetchAll();

//echo count($elements);

//$number2 = 1234.56;
//echo $formatted_number = number_format($number2, 2, ',', ' ');

foreach ($elements as $key => $element) {
    $numberDot = str_replace(',', '.', $element['IBLOCK_ELEMENTS_ELEMENT_OBJECTS_SUMM_VALUE']); // замена запятой на точку
    $strToNumber = floatval($numberDot); // меняем тип данных на число с плавающей точкой
    $priceFormat = number_format($strToNumber, 2, ',', ' ') . 'руб';

        echo '<pre>'; print_r($element); echo '</pre>';
//    echo 'число - '; echo floatval($strToNum); echo '<br>';
//    echo 'цена - ' .  number_format($strToNumber, 2, ',', ' ');
    echo '<pre>'; print_r(getCoordinates('Республика Башкортостан, город Уфа, улица Юрия Гагарина, 40', $element['IBLOCK_ELEMENTS_ELEMENT_OBJECTS_CITY_VALUE'])); echo '</pre>';
}



require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
