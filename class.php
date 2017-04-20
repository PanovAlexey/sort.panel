<?
/**
 * Created by Alexey Panov.
 * Date: 24.12.2016
 * Time: 11:00
 *
 * @author    Alexey Panov <panov@codeblog.pro>
 * @copyright Copyright 2016, Alexey Panov
 * @git repository https://github.com/PanovAlexey/sort.panel
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SystemException;

class CCodeblogProSortPanelComponent extends \CBitrixComponent
{

    protected $requiredModules = ['iblock'];

    /**
     * @return array
     */
    public static function getSortOrderList() {

        Loc::loadMessages(__FILE__);

        $sortingParams = [];

        $sortingParams['ORDERS_LIST'] = ['asc'        => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_ASC_VALUE'),
                                         'nulls,asc'  => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_NULL_ASC_VALUE'),
                                         'asc,nulls'  => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_ASC_NULLS_VALUE'),
                                         'desc'       => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_DESC_VALUE'),
                                         'nulls,desc' => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_NULLS_DESC_VALUE'),

                                         'desc,nulls' => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_DESC_NULLS_VALUE')];

        $sortingParams['ORDERS_DEFAULT_LIST'] = ['asc', 'desc'];

        $sortingParams['TYPES_LIST'] = [['NAME' => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_TYPES_NAME_VALUE'),
                                         'CODE' => 'name'],
                                        ['NAME' => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_TYPES_RAND_VALUE'),
                                         'CODE' => 'rand'],
                                        ['NAME' => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_TYPES_ACTIVE_VALUE'),
                                         'CODE' => 'active'],
                                        ['NAME' => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_TYPES_SORT_VALUE'),
                                         'CODE' => 'sort'],
                                        ['NAME' => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_TYPES_POPULAR_VALUE'),
                                         'CODE' => 'show_counter'],
                                        ['NAME' => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_TYPES_DATE_VALUE'),
                                         'CODE' => 'created']];

        $sortingParams['FIELDS_DEFAULT_LIST'] = ['name', 'sort', 'created'];

        return $sortingParams;
    }

    /**
     * @return array
     */
    public function getSortOrderListByCurrentFields() {

        $allFieldsList = self::getSortOrderList()['TYPES_LIST'];
        $fieldsList = array();

        foreach ($allFieldsList as $field) {
            if (in_array($field['CODE'], $this->arParams['FIELDS_CODE'])) {
                $fieldsList[$field['CODE']] = $field;
            }
        }

        return $fieldsList;
    }

    /**
     * @return array
     */
    public function getSortOrderListByCurrentProperties() {

        $propertyList = [];

        $propertiesCollection = \Bitrix\Iblock\PropertyTable::getList(['select' => ['NAME',
                                                                                    'CODE'],
                                                                       'filter' => ['IBLOCK_ID' => (int)$this->arParams['IBLOCK_ID'],
                                                                                    'CODE'      => $this->arParams['PROPERTY_CODE'],],]);
        while ($property = $propertiesCollection->fetch()) {
            $property['CODE'] = 'property_' . $property['CODE'];
            $propertyList[$property['CODE']]   = $property;
        }

        return $propertyList;
    }

    /**
     * @return array
     */
    public function getSortOrderListByCurrentPrices() {

        $propertyList = [];

        if (Loader::includeModule('catalog')) {
            $priceTypeCollection = \CCatalogGroup::GetList(['SORT' => 'ASC'], ['ID' => $this->arParams['PRICE_CODE']]);

            while ($priceType = $priceTypeCollection->Fetch()) {

                $property['NAME'] = $priceType['NAME_LANG'];
                $property['CODE'] = 'catalog_PRICE_' . $priceType['ID'];
                $propertyList[$property['CODE']]   = $property;
            }
        }

        return $propertyList;
    }

    protected function checkModules() {

        foreach ($this->requiredModules as $moduleName) {
            if (!Loader::includeModule($moduleName)) {
                throw new SystemException(Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_NO_MODULE', ['#MODULE#',
                                                                                                       $moduleName]));
            }
        }

        return $this;
    }

    /**
     * Event called from includeComponent before component execution.
     * Takes component parameters as argument and should return it formatted as needed.
     *
     * @param  array [string]mixed $arParams
     *
     * @return array[string]mixed
     */
    public function onPrepareComponentParams($params) {

        global ${$params['SORT_NAME']};

        if (trim($params['SORT_NAME']) == '') {
            $params['SORT_NAME'] = 'SORT';
        }

        if (!(${$params['SORT_NAME']})) {
            ${$params['SORT_NAME']} = [];
        }

        global ${$params['ORDER_NAME']};

        if (trim($params['ORDER_NAME']) == '') {
            $params['ORDER_NAME'] = 'ORDER';
        }

        if (!(${$params['ORDER_NAME']})) {
            ${$params['ORDER_NAME']} = [];
        }

        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }

        return $params;
    }

    /**
     * Event called from includeComponent before component execution.
     * Includes component.php from within lang directory of the component.
     *
     * @return void
     */
    public function onIncludeComponentLang() {
        $this->includeComponentLang(basename(__FILE__));
        Loc::loadMessages(__FILE__);
    }

    /**
     * @param      $value
     * @param bool $isOrder
     *
     * @return bool
     */
    protected function isSortActive($value, $isOrder = false) {

        $isOrder = boolval($isOrder);
        $value   = trim($value);

        $isActive = false;

        if ($isOrder) {

            if ($_REQUEST['order'] == $value) {
                $isActive = true;
            }

            if ((empty($_REQUEST['order'])) && ($_SESSION['order'] == $value)) {
                $isActive = true;
            }

        } else {
            if ($_REQUEST['sort'] == $value) {
                $isActive = true;
            }

            if ((empty($_REQUEST['sort'])) && ($_SESSION['sort'] == $value)) {
                $isActive = true;
            }
        }

        return $isActive;
    }

    /**
     * @return $this
     */
    protected function prepareResult() {

        global $USER;

        $cacheId = $_REQUEST['sort'] . $_REQUEST['order'];
        $cacheId .= serialize($this->arParams);

        if ($this->arParams['INCLUDE_SORT_TO_SESSION'] == 'Y') {
            $cacheId .= $_SESSION['sort'] . $_SESSION['order'];
        }

        $cacheId .= $USER->GetGroups();

        $cache = new CPHPCache();

        if ($cache->InitCache($this->arParams['CACHE_TIME'], $cacheId, '/sort.panel/')) {
            $result = $cache->GetVars();
        } elseif ($cache->StartDataCache()) {

            $result['SORT']['PROPERTIES'] = array();

            if ($this->arParams['FIELDS_CODE']) {
                $result['SORT']['PROPERTIES'] = array_merge(
                    $result['SORT']['PROPERTIES'], $this->getSortOrderListByCurrentFields()
                );
            }

            if ($this->arParams['PROPERTY_CODE']) {
                $result['SORT']['PROPERTIES'] = array_merge(
                    $result['SORT']['PROPERTIES'], $this->getSortOrderListByCurrentProperties()
                );
            }

            if ($this->arParams['PRICE_CODE']) {
                $result['SORT']['PROPERTIES'] = array_merge(
                    $result['SORT']['PROPERTIES'], $this->getSortOrderListByCurrentPrices()
                );
            }

            $cache->EndDataCache($result);
        }

        global $APPLICATION;

        foreach ($result['SORT']['PROPERTIES'] as &$prop) {

            $prop['URL'] = $APPLICATION->GetCurPageParam('sort=' . $prop['CODE'], ['sort']);

            $prop['ACTIVE'] = $this->isSortActive($prop['CODE']);
        }

        if (!empty($this->arParams['SORT_ORDER'])) {

            foreach ($this->arParams['SORT_ORDER'] as $sortOrder) {

                $result['SORT']['ORDERS'][] = ['ACTIVE' => $this->isSortActive($sortOrder, $isOrder = true),
                                               'CODE'   => $sortOrder,
                                               'URL'    => $APPLICATION->GetCurPageParam('order='
                                                                                         . $sortOrder, ['order'])];
            }

        }

        $this->arResult = $result;

        return $this;
    }

    /**
     * @return void
     */
    protected function outputtingSortingParameters() {

        global ${$this->arParams['SORT_NAME']};
        global ${$this->arParams['ORDER_NAME']};

        if ($this->arParams['INCLUDE_SORT_TO_SESSION'] == 'Y') {

            if (empty($_REQUEST['sort'])) {
                ${$this->arParams['SORT_NAME']} = $_SESSION['sort'];
            } else {
                $_SESSION['sort']               = $_REQUEST['sort'];
                ${$this->arParams['SORT_NAME']} = $_REQUEST['sort'];
            }

            if (empty($_REQUEST['order'])) {
                ${$this->arParams['ORDER_NAME']} = $_SESSION['order'];
            } else {
                $_SESSION['order']               = $_REQUEST['order'];
                ${$this->arParams['ORDER_NAME']} = $_REQUEST['order'];
            }
        } else {
            ${$this->arParams['SORT_NAME']}  = $_REQUEST['sort'];
            ${$this->arParams['ORDER_NAME']} = $_REQUEST['order'];
        }

    }

    public function executeComponent() {
        try {
            $this->checkModules()->prepareResult();
            $this->outputtingSortingParameters();
            $this->includeComponentTemplate();
        } catch (SystemException $e) {
            self::__showError($e->getMessage());
        }
    }
}