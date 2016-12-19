<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}


use Bitrix\Main\Localization\Loc;

if (!empty($arResult['SORT']['PROPERTIES'])) {
    ?>
    Сортировать по:<br>
    <? foreach ($arResult['SORT']['PROPERTIES'] as $prop) { ?>
        <a href="<?= $prop['URL']; ?>"><?= $prop['NAME'] ?></a>&nbsp
        <?
    } ?>

<? } ?>
<br><br>
<? if (!empty($arResult['SORT']['ORDERS'])) { ?>
    Направление сортировки<br><br><?
    foreach ($arResult['SORT']['ORDERS'] as $order) {
        ?>
        <a href="<?= $order['URL']; ?>"><?= $order['CODE'] ?></a>&nbsp
        <?
    }
} ?>
