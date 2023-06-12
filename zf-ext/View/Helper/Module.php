<?php
namespace Zf\Ext\View\Helper;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Zf\Ext\View\Helper;
class Module
{
    /**
     * Retrieve default zend-db configuration for zend-mvc context.
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'view_helpers' => [
                'factories' => [
                    Helper\LazyAsset::class                   => InvokableFactory::class,
                    Helper\Url::class                         => Helper\HelperFactory::class,
                    Helper\MinifyHeadLink::class              => InvokableFactory::class,
                    Helper\MinifyHeadScript::class            => InvokableFactory::class,
                    Helper\CsrfToken::class                   => Helper\HelperFactory::class,
             
                    BootstrapToolbar::class                   => InvokableFactory::class,
                    BootstrapToolbar\ToolbarIcon::class       => InvokableFactory::class,
                    BootstrapToolbar\ToolbarInsert::class     => ToolbarFactory::class,
                    BootstrapToolbar\ToolbarDelete::class     => ToolbarFactory::class,
                    BootstrapToolbar\ToolbarClose::class       => ToolbarFactory::class,
                    BootstrapToolbar\ToolbarSave::class       => ToolbarFactory::class,
             
                    BootstrapManage\ManageIcon::class         => InvokableFactory::class,
                    BootstrapManage\ManageCheckbox::class     => InvokableFactory::class,
                    BootstrapManage\ManageCheckboxAll::class  => InvokableFactory::class,
                    BootstrapManage\ManageUpdate::class       => InvokableFactory::class,
                    BootstrapManage\ManageDetail::class       => InvokableFactory::class,
                    BootstrapManage\ManageDelete::class       => InvokableFactory::class,
                    BootstrapManage\ManageChangeStatus::class => InvokableFactory::class,

                    Helper\HeadScriptAction::class            => Helper\HelperFactory::class,
                    Helper\HeadStyleAction::class             => Helper\HelperFactory::class,
                    Helper\Authen::class                      => Helper\HelperFactory::class,
                ],
                'aliases' => [
                    'lazyAsset'                     => Helper\LazyAsset::class,
                    'zfUrl'                         => Helper\Url::class,
                    'minifyHeadLink'                => Helper\MinifyHeadLink::class,
                    'minifyHeadScript'              => Helper\MinifyHeadScript::class,
                    Helper\CsrfToken::SERVICE_ALIAS => Helper\CsrfToken::class,

                    'bootstrapToolbar'              => BootstrapToolbar::class,
                    'toolbarIcon'                   => BootstrapToolbar\ToolbarIcon::class,
                    'toolbarInsert'                 => BootstrapToolbar\ToolbarInsert::class,
                    'toolbarDelete'                 => BootstrapToolbar\ToolbarDelete::class,
                    'toolbarClose'                  => BootstrapToolbar\ToolbarClose::class,
                    'toolbarSave'                   => BootstrapToolbar\ToolbarSave::class,
                            
                    'manageIcon'                    => BootstrapManage\ManageIcon::class,
                    'manageCheckbox'                => BootstrapManage\ManageCheckbox::class,
                    'manageCheckboxAll'             => BootstrapManage\ManageCheckboxAll::class,
                    'manageUpdate'                  => BootstrapManage\ManageUpdate::class,
                    'manageDetail'                  => BootstrapManage\ManageDetail::class,
                    'manageDelete'                  => BootstrapManage\ManageDelete::class,
                    'manageChangeStatus'            => BootstrapManage\ManageChangeStatus::class,
                            
                    'headScriptAction'              => Helper\HeadScriptAction::class,
                    'headStyleAction'               => Helper\HeadStyleAction::class,
                    'zfAuthen'                      => Helper\Authen::class,
                ]
            ]
        ];
    }
}
