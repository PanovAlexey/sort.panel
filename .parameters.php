<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

Loader::includeModule('iblock');
Loader::includeModule('catalog');

/**
 * Getting iblock types list and iblocks list
 */
$iblockElementTypeList = \CIBlockParameters::GetIBlockTypes();

$iblockElementList        = [];
$iblockElementFilter      = (!empty($arCurrentValues['IBLOCK_TYPE']) ? ['TYPE'   => $arCurrentValues['IBLOCK_TYPE'],
                                                                        'ACTIVE' => 'Y',] : ['ACTIVE' => 'Y']);
$iblockElementsCollection = \CIBlock::GetList(['SORT' => 'ASC'], $iblockElementFilter);
while ($iblockElement = $iblockElementsCollection->Fetch())
    $iblockElementList[$iblockElement['ID']] = '[' . $iblockElement['ID'] . '] ' . $iblockElement['NAME'];

/**
 *  Getting properties list for sorting
 */
$propertyList         = [];
$propertiesCollection = \CIBlockProperty::GetList(['sort' => 'asc',
                                                   'name' => 'asc'], ['ACTIVE'    => 'Y',
                                                                      'MULTIPLE'  => 'N',
                                                                      'IBLOCK_ID' => (isset($arCurrentValues['IBLOCK_ID'])
                                                                          ? $arCurrentValues['IBLOCK_ID']
                                                                          : $arCurrentValues['ID']),]);
while ($propertyElement = $propertiesCollection->Fetch()) {
    $arProperty[$propertyElement['CODE']] = '[' . $propertyElement['CODE'] . '] ' . $propertyElement['NAME'];
    if (in_array($propertyElement['PROPERTY_TYPE'], ['N',
                                                     'L',
                                                     'S'])) {
        $propertyList[$propertyElement['CODE']] = '[' . $propertyElement['CODE'] . '] ' . $propertyElement['NAME'];
    }
}

/**
 * Getting priceslist for sorting
 */
$priceTypeCollection = \CCatalogGroup::GetList(['SORT' => 'ASC'], []);
while ($priceType = $priceTypeCollection->Fetch()) {
    $priceList[$priceType['ID']] = '[' . 'catalog_PRICE_' . $priceType['ID'] . '] ' . $priceType['NAME_LANG'];
}

include 'class.php';
$sortOrdersList = CCodeblogSortPanelComponent::getSortOrderList()['ORDERS_LIST'];

$sortOrdersDefaultList = CCodeblogSortPanelComponent::getSortOrderList()['ORDERS_DEFAULT_LIST'];

$arComponentParameters = ['GROUPS'     => [],
                          'PARAMETERS' => ['IBLOCK_TYPE'             => ['PARENT'  => 'DATA_SOURCE',
                                                                         'NAME'    => Loc::getMessage('SORT_PANEL_IBLOCK_TYPE_TITLE'),
                                                                         'TYPE'    => 'LIST',
                                                                         'VALUES'  => $iblockElementTypeList,
                                                                         'REFRESH' => 'Y',],
                                           'IBLOCK_ID'               => ['PARENT'            => 'DATA_SOURCE',
                                                                         'NAME'              => Loc::getMessage('SORT_PANEL_IBLOCK_ID_TITLE'),
                                                                         'TYPE'              => 'LIST',
                                                                         'ADDITIONAL_VALUES' => 'Y',
                                                                         'VALUES'            => $iblockElementList,
                                                                         'REFRESH'           => 'Y',],
                                           'PROPERTY_CODE'           => ['PARENT'            => 'DATA_SOURCE',
                                                                         'NAME'              => Loc::getMessage('SORT_PANEL_PROPERTY_CODE_TITLE'),
                                                                         'TYPE'              => 'LIST',
                                                                         'MULTIPLE'          => 'Y',
                                                                         'VALUES'            => $propertyList,
                                                                         'ADDITIONAL_VALUES' => 'N',],
                                           'PRICE_CODE'              => ['PARENT'            => 'DATA_SOURCE',
                                                                         'NAME'              => Loc::getMessage('SORT_PANEL_PRICE_CODE_TITLE'),
                                                                         'TYPE'              => 'LIST',
                                                                         'MULTIPLE'          => 'Y',
                                                                         'VALUES'            => $priceList,
                                                                         'ADDITIONAL_VALUES' => 'N',],
                                           'SORT_ORDER'              => ['PARENT'            => 'DATA_SOURCE',
                                                                         'NAME'              => Loc::getMessage('SORT_PANEL_SORT_ORDER_TITLE'),
                                                                         'TYPE'              => 'LIST',
                                                                         'MULTIPLE'          => 'Y',
                                                                         'VALUES'            => $sortOrdersList,
                                                                         'DEFAULT'           => $sortOrdersDefaultList,
                                                                         'ADDITIONAL_VALUES' => 'N',],
                                           'INCLUDE_SORT_TO_SESSION' => ['PARENT'  => 'ADDITIONAL_SETTINGS',
                                                                         'NAME'    => Loc::getMessage('SORT_PANEL_INCLUDE_SORT_TO_SESSION'),
                                                                         'TYPE'    => 'CHECKBOX',
                                                                         'DEFAULT' => 'Y',],
                                           'SORT_NAME'               => ['PARENT'  => 'DATA_SOURCE',
                                                                         'NAME'    => Loc::getMessage('SORT_PANEL_CODE_SORT_RETURN'),
                                                                         'TYPE'    => 'STRING',
                                                                         'DEFAULT' => 'SORT',],
                                           'ORDER_NAME'              => ['PARENT'  => 'DATA_SOURCE',
                                                                         'NAME'    => Loc::getMessage('SORT_PANEL_CODE_ORDER_RETURN'),
                                                                         'TYPE'    => 'STRING',
                                                                         'DEFAULT' => 'ORDER',],
                                           'CACHE_TIME'              => ['DEFAULT' => 36000000],],];