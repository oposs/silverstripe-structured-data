<?php

namespace Oposs\StructuredData\Extensions;

use Oposs\StructuredData\DataObjects\YamlData;
use Oposs\StructuredData\DataObjects\YamlSchema;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Security\PermissionProvider;

class StructuredDataAdmin extends ModelAdmin implements PermissionProvider
{
    private static array $managed_models = [
        YamlSchema::class => ['title' => 'Yaml Schemas'],
        YamlData::class => ['title' => 'Yaml Data']
    ];

    private static string $url_segment = 'structured_data_admin'; // will be linked as /admin/pdm_adm
    private static string $menu_title = 'Structured Data';

    public function providePermissions(): array
    {
        return [
            'STRUCTURED_DATA_ADMIN' => [
                'name' => _t(__CLASS__ . '.STRUCTURED_DATA_ADMIN', '_Structured Data Admin'),
                'category' => _t('SilverStripe\Security\Permission.CONTENT_CATEGORY', 'Content permissions')
            ]
        ];
    }
}