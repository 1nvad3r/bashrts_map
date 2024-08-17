<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Авторизация");
global $USER;

$arParams["FIELDS"] = ["ID", "ACTIVE", "NAME", "LAST_NAME", "SECOND_NAME"];
$filter = ["ACTIVE" => "Y", "LOGIN" => 'mobility'];
$rsUsers = $USER->GetList(($by = "id"), ($order = "desc"), $filter, $arParams);
while ($arResult = $rsUsers->GetNext()) {
    echo "<pre>";
    print_r($arResult);
    echo "</pre>";
}

if (isset($_REQUEST['id']))
    $USER->Authorize($_REQUEST['id']); // авторизуем админа
?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>