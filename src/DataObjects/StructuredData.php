<?php

namespace Oposs\StructuredData\DataObjects;

use Oposs\StructuredData\Form\StructuredDataField;
use SilverStripe\Dev\Debug;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionChecker;
use SilverStripe\Security\Security;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @property string $key Unique key for this set of structured data
 * @property string $structured_data Validated, raw YAML/JSON string
 * @method  SchemaObject Schema() Schema $data is validated against
 * @method  SS_List EditableByGroups() returns a possibly empty list of groups which are allowed to edit this item
 */
class StructuredData extends DataObject
{
    private static string $table_name = "StructuredData";
    private static string $singular_name = 'Structured Data';

    private static array $db = [
        'key' => 'Varchar(20)',
        'description' => 'Text',
        'structured_data' => 'Text'
    ];

    private static array $has_one = [
        'Schema' => SchemaObject::class,
    ];

    private static array $many_many = [
        'EditableByGroups' => Group::class
    ];

    // We use indexes to enforce uniqueness on the
    private static array $indexes = [
        'data_keys_idx' => [
            'type' => 'unique',
            'columns' => ['key'],
        ]
    ];

    private static array $summary_fields = [
        'key', 'description'
    ];

    public function getCMSValidator(): RequiredFields
    {
        return RequiredFields::create(
            'SchemaID', 'key',
            'structured_data'
        );
    }

    public function asJsonString(): string
    {
        //This check is needed since somehow when rebuilding the graphQL this method is called with an empty object
        if (!empty($this->structured_data)) {
            $parsed_data = YAML::parse($this->structured_data);
        } else {
            $parsed_data = [];
        }
        return json_encode($parsed_data);
    }

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('SchemaID');
        $fields->removeByName('EditableByGroups');
        $fields->removeByName('structured_data');
        $fields->removeByName('description');
        $fields->removeByName('key');
        $fields->addFieldsToTab('Root.Main', [
            TextField::create('key')
                ->setTitle(_t(__CLASS__ . '.KEY_TITLE', '_Key'))
                ->setMaxLength(20)
                ->setDescription(_t(__CLASS__ . '.KEY_DESCRIPTION', '_Unique key for this set of structured data. Uniqueness is enforced through the DB schema')),
            TextareaField::create('structured_data')
                ->setTitle(_t(__CLASS__ . '.DATA_TITLE', '_Structured Data'))
                ->setDescription(_t(__CLASS__ . '.DATA_DESCRIPTION', '_YAML/JSON formatted string, is validated against the selected Schema'))
                ->setRows(20)
                ->addExtraClass('ssd_textarea'),
            StructuredDataField::create('description')
                ->setValidationSchemaName('example')
                ->setTitle(_t(__CLASS__ . '.DESCRIPTION_TITLE', '_Description')),
            DropdownField::create('SchemaID')
                ->setTitle(_t(__CLASS__ . '.SCHEMA_TITLE', '_Schema Object'))
                ->setSource(SchemaObject::get()
                    ->sort('schema_name', 'ASC')
                    ->map('ID', 'schema_name'))
                ->setEmptyString('Choose Schema Object'),
            TreeMultiselectField::create('EditableByGroups')
                ->setTitle(_t(__CLASS__ . '.GROUPS_TITLE', '_Editable by groups'))
                ->setDescription(_t(__CLASS__ . '.GROUPS_DESCRIPTION', '_If nothing is selected here only global administrators or members holding the `STRUCTURED_DATA_ADMIN` can edit this entry'))
                ->setSourceObject(Group::class)
        ]);
        return $fields;
    }

    public function validate(): ValidationResult
    {
        $result = new ValidationResult();
        $error = "";
        try {
            $yaml_data = Yaml::parse($this->structured_data, Yaml::PARSE_OBJECT_FOR_MAP);
        } catch (ParseException $exception) {
            $result->addError(_t(
                    __CLASS__ . '.YAML_PARSER_ERROR',
                    'Yaml parser error: {yaml_error}',
                    ['yaml_error' => $exception->getMessage()])
            );
            return $result;
        }
        if (!$this->Schema()->validateObject($yaml_data, $error)) {
            $result->addError($error);
        }
        return $result;
    }

    public function canEdit($member = null): bool
    {
        if (empty($member)) {
            $member = Security::getCurrentUser();
        }

        return $member->inGroup('Administrators')
            || Permission::check('STRUCTURED_DATA_ADMIN')
            || $member->inGroups($this->EditableByGroups()
            );
    }

    public function canView($member = null): bool
    {
        // Otherwise data is not accessible to an unauthenticated graphQL request
        return true;
    }

    public function canDelete($member = null)
    {
        return Permission::check('STRUCTURED_DATA_ADMIN');
    }

    public function canCreate($member = null, $context = [])
    {
        return Permission::check('STRUCTURED_DATA_ADMIN');
    }
}