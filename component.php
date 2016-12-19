<?
/**
 * Created by PhpStorm.
 * Date: 24.06.2016
 * Time: 11:00
 *
 * @author    Alexey Panov <alexeykapanov@gmail.com>
 * @copyright Copyright © 2016, Alexey Panov
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;

Loader::includeModule('iblock');
Loader::includeModule('catalog');

$this->setFrameMode(true);
global $APPLICATION;

global ${$arParams['SORT_NAME']};

if (trim($arParams['SORT_NAME']) == '') {
    $arParams['SORT_NAME'] = 'SORT';
}

if (!(${$arParams['SORT_NAME']})) {
    ${$arParams['SORT_NAME']} = [];
}

global ${$arParams['ORDER_NAME']};

if (trim($arParams['ORDER_NAME']) == '') {
    $arParams['ORDER_NAME'] = 'ORDER';
}

if (!(${$arParams['ORDER_NAME']})) {
    ${$arParams['ORDER_NAME']} = [];
}

$arResult                               = [];
$arResult['OTHER_PROPS']['PREFIX_NAME'] = htmlspecialchars(trim($arParams['SORT_NAME_ADD_CODE']));

if (!isset($arParams['CACHE_TIME'])) {
    $arParams['CACHE_TIME'] = 36000000;
}
$cacheId = $_REQUEST['sort' . $arResult['OTHER_PROPS']['PREFIX_NAME']] . ' ';
$cacheId .= $_REQUEST['order' . $arResult['OTHER_PROPS']['PREFIX_NAME']] . ' ';
$cacheId .= $USER->GetGroups();

if ($this->StartResultCache(false, $cacheId)) {
    //Добавим стандартные поля для сортировки элементво инфоблока
    $arResult['SORT']['PROPERTIES'][] = ['NAME' => 'По названию',
                                         'CODE' => 'name'];
    $arResult['SORT']['PROPERTIES'][] = ['NAME' => 'В случайном порядке',
                                         'CODE' => 'rand'];
    $arResult['SORT']['PROPERTIES'][] = ['NAME' => 'Признак активности элемента',
                                         'CODE' => 'active'];
    $arResult['SORT']['PROPERTIES'][] = ['NAME' => 'По индексу сортировки',
                                         'CODE' => 'sort'];
    $arResult['SORT']['PROPERTIES'][] = ['NAME' => 'По популярности',
                                         'CODE' => 'show_counter'];
    $arResult['SORT']['PROPERTIES'][] = ['NAME' => 'По новизне',
                                         'CODE' => 'created'];

    if ($arParams['PROPERTY_CODE']) {
        //Сформируем массив,выбранных свойств (CODE,NAME)
        $resProps = \Bitrix\Iblock\PropertyTable::getList(['select' => ['NAME',
                                                                        'CODE'],
                                                           'filter' => ['IBLOCK_ID' => intval($arParams['IBLOCK_ID']),
                                                                        'CODE'      => $arParams['PROPERTY_CODE'],],]);
        while ($row = $resProps->fetch()) {
            $row['CODE']                      = 'property_' . $row['CODE'];
            $arResult['SORT']['PROPERTIES'][] = $row;
        }
    }

    if ($arParams['PRICE_CODE']) {
        $priceTypeCollection = \CCatalogGroup::GetList(['SORT' => 'ASC'], ['ID' => $arParams['PRICE_CODE']]);
        while ($priceType = $priceTypeCollection->Fetch()) {
            $row['NAME']                      = $priceType['NAME_LANG'];
            $row['CODE']                      = 'catalog_PRICE_' . $priceType['ID'];
            $arResult['SORT']['PROPERTIES'][] = $row;
        }
    }

    //Сформируем URL и добавим флаг активности
    foreach ($arResult['SORT']['PROPERTIES'] as &$prop) {

        $prop['URL'] = $APPLICATION->GetCurPageParam('sort' . $arResult['OTHER_PROPS']['PREFIX_NAME'] . '='
                                                     . $prop['CODE'], ['sort'
                                                                       . $arResult['OTHER_PROPS']['PREFIX_NAME']]);
        $bActive     = false;
        if (htmlspecialchars($_REQUEST['sort' . $arResult['OTHER_PROPS']['PREFIX_NAME']] == $prop['CODE'])) {
            $bActive = true;
        }
        $prop['ACTIVE'] = $bActive;
    }

    if (!empty($arParams['SORT_ORDER'])) {
        foreach ($arParams['SORT_ORDER'] as $sOrder) {
            $bActive = false;
            if (htmlspecialchars($_REQUEST['order' . $arResult['OTHER_PROPS']['PREFIX_NAME']] == $sOrder)) {
                $bActive = true;
            }
            $arResult['SORT']['ORDERS'][] = ['ACTIVE' => $bActive,
                                             'CODE'   => $sOrder,
                                             'URL'    => $APPLICATION->GetCurPageParam('order'
                                                                                       . $arResult['OTHER_PROPS']['PREFIX_NAME']
                                                                                       . '=' . $sOrder, ['order'
                                                                                                         . $arResult['OTHER_PROPS']['PREFIX_NAME']]),];
        }
    }

    $this->IncludeComponentTemplate();
}

if ($arParams['INCLUDE_SORT_TO_SESSION'] == 'Y') {
    if (empty($_REQUEST['sort' . $arResult['OTHER_PROPS']['PREFIX_NAME']])) {
        ${$arParams['SORT_NAME']}                                   = $_SESSION['sort'
                                                                                . $arResult['OTHER_PROPS']['PREFIX_NAME']];
        $_SESSION['sort' . $arResult['OTHER_PROPS']['PREFIX_NAME']] = $_SESSION['sort'
                                                                                . $arResult['OTHER_PROPS']['PREFIX_NAME']];
    } else {
        $_SESSION['sort' . $arResult['OTHER_PROPS']['PREFIX_NAME']] = $_REQUEST['sort'
                                                                                . $arResult['OTHER_PROPS']['PREFIX_NAME']];
        ${$arParams['SORT_NAME']}                                   = $_REQUEST['sort'
                                                                                . $arResult['OTHER_PROPS']['PREFIX_NAME']];
    }

    if (empty($_REQUEST['order' . $arResult['OTHER_PROPS']['PREFIX_NAME']])) {
        $_SESSION['order' . $arResult['OTHER_PROPS']['PREFIX_NAME']] = $_SESSION['order'
                                                                                 . $arResult['OTHER_PROPS']['PREFIX_NAME']];
        ${$arParams['ORDER_NAME']}                                   = $_SESSION['order'
                                                                                 . $arResult['OTHER_PROPS']['PREFIX_NAME']];
    } else {
        $_SESSION['order' . $arResult['OTHER_PROPS']['PREFIX_NAME']] = $_REQUEST['order'
                                                                                 . $arResult['OTHER_PROPS']['PREFIX_NAME']];
        ${$arParams['ORDER_NAME']}                                   = $_REQUEST['order'
                                                                                 . $arResult['OTHER_PROPS']['PREFIX_NAME']];
    }
} else {
    ${$arParams['SORT_NAME']}  = $_REQUEST['sort' . $arResult['OTHER_PROPS']['PREFIX_NAME']];
    ${$arParams['ORDER_NAME']} = $_REQUEST['order' . $arResult['OTHER_PROPS']['PREFIX_NAME']];
}