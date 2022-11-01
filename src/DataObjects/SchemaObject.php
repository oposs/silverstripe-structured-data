<?php

namespace Oposs\StructuredData\DataObjects;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Permission;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Opis\JsonSchema\{
    Validator
};


/**
 * @property string $schema_name Name of this schema
 * @property string $schema_data Json schema
 */
class SchemaObject extends DataObject
{

    private static string $table_name = "SchemaObject";
    private static string $singular_name = 'Schema';

    private static array $db = [
        'schema_name' => 'Varchar(10)',
        'schema_data' => 'Text'
    ];

    private static array $field_labels = [
        'schema_name' => 'Schema Name',
        'schema_data' => 'Schema Definition'
    ];

    private static array $summary_fields = [
        'ID', 'schema_name'
    ];


    public function validate(): ValidationResult
    {
        $result = new ValidationResult();
        try {
            Yaml::parse($this->schema_data);
        } catch (ParseException $e) {
            $result->addError(_t(
                    __CLASS__ . '.YAML_PARSER_ERROR',
                    'Yaml parser error: {yaml_error}',
                    ['yaml_error' => $e->getMessage()])
            );
            return $result;
        }
        return $result;
    }

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('schema_data');
        $fields->addFieldsToTab('Root.Main', [
            TextareaField::create('schema_data')
                ->setTitle(_t(__CLASS__ . '.SCHEMA_TITLE', '_Schema Definition'))
                ->setDescription(_t(__CLASS__ . '.SCHEMA_DESCRIPTION', '_Either YAML or JSON Schema'))
                ->setRows(20)
                ->addExtraClass('ssd_textarea'),
        ]);
        return $fields;
    }

    /**
     * Validates a PHP object against this JSON Schema
     *
     * Notes:
     *  - When using json_decode() to parse a JSON string, make sure that associative is set to false
     *  - When using YAML::parse() set Yaml::PARSE_OBJECT_FOR_MAP
     *
     * @param object $object Object to validate against this JSON schema.
     * @param string $error Reference to possible error message
     * @return bool True on success, False on failure
     */
    public function validateObject(object $object, string &$error): bool
    {
        $validator = new Validator();
        $validation_result = $validator->validate($object, $this->schema_data);
        if ($validation_result->isValid()) {
            return true;
        } else {
            $error = _t(
                __CLASS__ . '.DATA_DOES_NOT_COMPLY_SCHEMA',
                '_Data does not comply with schema: {schema_name}, error: {validator_error}',
                ["validator_error" => $validation_result->error(), "schema_name" => $this->schema_name]
            );
            return false;
        }
    }

    public function canView($member = null)
    {
        return Permission::check('STRUCTURED_DATA_VIEW');
    }

    public function canEdit($member = null)
    {
        return Permission::check('STRUCTURED_DATA_ADMIN');
    }

    public function canDelete($member = null)
    {
        return Permission::check('STRUCTURED_DATA_ADMIN');
    }

}