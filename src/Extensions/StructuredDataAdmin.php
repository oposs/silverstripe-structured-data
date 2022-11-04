<?php

namespace Oposs\StructuredData\Extensions;

use Oposs\StructuredData\DataObjects\SchemaObject;
use Oposs\StructuredData\DataObjects\StructuredData;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;

class StructuredDataAdmin extends ModelAdmin implements PermissionProvider
{
    private static array $managed_models = [
        StructuredData::class => ['title' => 'Structured Data'],
        SchemaObject::class => ['title' => 'Schema Definitions']
    ];

    private static string $url_segment = 'structured_data_admin';
    private static string $menu_title = 'Structured Data';

    public function providePermissions(): array
    {
        $category = _t(__CLASS__ . '.PERMISSION_GROUP_NAME', '_Structured Data');
        return [
            'STRUCTURED_DATA_VIEW' => [
                'name' => _t(__CLASS__ . '.VIEW_PERMISSION_NAME', 'View Structured Data Module'),
                'category' => $category
            ],
            'STRUCTURED_DATA_ADMIN' => [
                'name' => _t(__CLASS__ . '.ADMIN_PERMISSION_NAME', 'Edit schemas'),
                'category' => $category
            ],
        ];
    }

    public function canView($member = null): bool
    {
        return Permission::check('STRUCTURED_DATA_VIEW');
    }
}