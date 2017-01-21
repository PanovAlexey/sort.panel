<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
$arComponentDescription = ['NAME'        => Loc::getMessage('SORT_PANEL_COMPONENT_NAME_VALUE'),
                           'DESCRIPTION' => Loc::getMessage('SORT_PANEL_COMPONENT_DESCRIPTION_VALUE'),
                           'ICON'        => '/images/icon.gif',
                           'SORT'        => 10,
                           'CACHE_PATH'  => 'Y',
                           'PATH'        => ['ID'    => 'codeblog.pro',
                                             'CHILD' => ['ID'   => 'Content',
                                                         'NAME' => Loc::getMessage('SORT_PANEL_COMPONENT_TYPE_CONTENT_VALUE'),],],
                           'COMPLEX'     => 'N',];
