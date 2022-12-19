<?php

namespace Oposs\StructuredData\DataObjects;

use Exception;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Permission;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Opis\JsonSchema\{Errors\ValidationError, Validator};


/**
 * @property string $schema_name Name of this schema
 * @property string $schema_data Json schema
 */
class SchemaObject extends DataObject
{

    private static string $table_name = "SchemaObject";
    private static string $singular_name = 'Schema';

    private static array $db = [
        'schema_name' => 'Varchar(20)',
        'schema_data' => 'Text'
    ];

    private static array $field_labels = [
        'schema_name' => 'Schema Name',
        'schema_data' => 'Schema Definition'
    ];

    private static array $summary_fields = [
        'ID', 'schema_name'
    ];

    public function getCMSValidator(): RequiredFields
    {
        return RequiredFields::create(
            'schema_name', 'schema_name'
        );
    }


    public function validate(): ValidationResult
    {
        $result = new ValidationResult();
        $error = "";
        // We do this trick to "validate" the schema itself (note: This is a very basic validation!)
        $dummy_data = Yaml::parse("age: 10", Yaml::PARSE_OBJECT_FOR_MAP);
        if (!self::validateAgainstSchema($dummy_data, $this->schema_data, $error, $this->schema_name, true)) {
            $result->addError($error);
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
     * @param object|string $object to validate against this JSON schema.
     * @param string $error Reference to possible error message
     * @return bool True on success, False on failure
     */
    public function validateObject($object, string &$error): bool
    {
        return self::validateAgainstSchema($object, $this->schema_data, $error, $this->schema_name);
    }

    /**
     * @param object|string $object to validate against this JSON schema.
     * @param string $schema String representation of a JSON/YAML formatted schema
     * @param string $error Reference to error string
     * @param string $schema_name (Optional) Schema name -> used in error messages
     * @return bool True if success, False if either an error happened or the $object does not match $schema
     */
    public static function validateAgainstSchemaString($object, string $schema, string &$error, string $schema_name = ''): bool
    {
        return self::validateAgainstSchema($object, $schema, $error, $schema_name);
    }

    /**
     * @param object|string $object Data to validate
     * @param string $schema String representation of a JSON/YAML formatted schema
     * @param string $error Reference to error string
     * @param string $schema_name (Optional) Schema name
     * @param bool $ignore_invalid_data If set to true (default: false) we ignore data validation errors
     * @return bool True if success, False if either an error happened or the $object does not match $schema
     */
    private static function validateAgainstSchema($object, string $schema, string &$error, string $schema_name = '', bool $ignore_invalid_data = false): bool
    {
        if (is_string($object)) {
            try {
                $object = YAML::parse($object, YAML::PARSE_OBJECT_FOR_MAP);
            } catch (ParseException $parseException) {
                $error = _t(
                    __CLASS__ . '.COULD_NOT_PARSE_DATA',
                    '_Could not parse data: {yaml_error}',
                    ["yaml_error" => $parseException->getMessage()]
                );
                return false;
            }
        }
        try {
            $parsed_schema = Yaml::parse($schema, YAML::PARSE_OBJECT_FOR_MAP);
            $validator = new Validator();
            $validation_result = $validator->validate($object, $parsed_schema);
            if ($ignore_invalid_data || $validation_result->isValid()) {
                return true;
            } else {
                $error = _t(
                    __CLASS__ . '.DATA_DOES_NOT_COMPLY_SCHEMA',
                    '_Data does not comply with schema: {schema_name}, error: {validator_error}',
                    ["validator_error" => $validation_result->error(), "schema_name" => $schema_name]
                );
                return false;
            }
        } catch (ParseException $parseException) {
            $error = _t(
                __CLASS__ . '.COULD_NOT_PARSE_SCHEMA',
                '_Could not parse schema `{schema_name}`, error: {parser_error}',
                ["schema_name" => $schema_name, "parser_error" => $parseException->getMessage()]
            );
            return false;
        } catch (Exception $e) {
            $error = _t(
                __CLASS__ . '.GENERAL_ERROR',
                '_An exception was thrown: {exception_message}',
                ["exception_message" => $e->getMessage()]
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