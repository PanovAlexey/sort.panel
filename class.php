<?
/**
 * Created by PhpStorm.
 * Date: 24.11.2016
 * Time: 11:00
 *
 * @author    Alexey Panov <alexeykapanov@gmail.com>
 * @copyright Copyright © 2016, Alexey Panov
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SystemException;

class CCodeblogSortPanelComponent extends \CBitrixComponent
{

    /**
     * @return array
     */
    public static function getSortOrderList() {

        $sortingParams = [];

        $sortingParams['ORDERS_LIST'] = ['asc'        => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_ASC_VALUE'),
                                         'nulls,asc'  => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_NULL_ASC_VALUE'),
                                         'asc,nulls'  => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_ASC_NULLS_VALUE'),
                                         'desc'       => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_DESC_VALUE'),
                                         'nulls,desc' => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_NULLS_DESC_VALUE'),
                                         'desc,nulls' => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_DESC_NULLS_VALUE')];

        $sortingParams['ORDERS_DEFAULT_LIST'] = ['asc'  => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_ASC_VALUE'),
                                                 'desc' => Loc::getMessage('COMPONENT_SORT_PANEL_COMPONENT_SORT_ORDER_DESC_VALUE')];

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

        return $sortingParams;
    }

    protected $requiredModules = ['iblock'];

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

    protected function prepareResult() {

        global $USER;

        $cacheId = $_REQUEST['sort'] . ' ';
        $cacheId .= $_REQUEST['order'] . ' ';
        $cacheId .= $USER->GetGroups();

        if ($this->StartResultCache(false, $cacheId)) {
            $this->IncludeComponentTemplate();
        }

        return $this;
    }

    public function executeComponent() {

        global $APPLICATION;

        try {
            $this->checkModules()->prepareResult();
            $this->includeComponentTemplate();
        } catch (SystemException $e) {
            self::__showError($e->getMessage());
        }
    }
}