<?php

namespace Oposs\StructuredData\DataObjects;

use SilverStripe\Dev\Debug;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\PermissionChecker;
use SilverStripe\Security\Security;

/**
 * @property string $key Unique key for this set of structured data
 * @property string $data Validated, raw YAML string
 * @method  YamlSchema Schema() Schema $data is validated against
 * @method  SS_List EditableByGroups() returns a possibly empty list of groups which are allowed to edit this item
 */
class YamlData extends DataObject
{
    private static string $table_name = "YamlData";
    private static string $singular_name = 'Yaml Data';

    private static array $db = [
        'key' => 'Varchar(10)',
        'description' => 'Text',
        'data' => 'Text'
    ];

    private static array $has_one = [
        'Schema' => YamlSchema::class,
    ];

    private static array $many_many = [
        'EditableByGroups' => Group::class
    ];

    // We use indexes to enforce uniqueness on the
    private static array $indexes = [
        'yaml_data_keys_idx' => [
            'type' => 'unique',
            'columns' => ['key'],
        ]
    ];

    private static array $summary_fields = [
        'key', 'description'
    ];

    public function asJsonString(): string
    {
        return json_encode($this->data);
    }

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('EditableByGroups');
        $fields->add(
            TreeMultiselectField::create(
                "EditableByGroups",
                'Editable by groups',
                Group::class
            ),
        );
        return $fields;
    }

    public function validate(): ValidationResult
    {
        $result = new ValidationResult();
        $error = "";
        if (!$this->Schema()->validateData($this->data, $error)) {
            $result->addError($error);
        }
        return $result;
    }

    public function canEdit($member = null): bool
    {
        if (empty($member)) {
            $member = Security::getCurrentUser();
        }
        return $member->inGroups($this->EditableByGroups());
    }
}