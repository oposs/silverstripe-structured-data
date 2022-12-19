<?php

namespace Oposs\StructuredData\Form;

use Oposs\StructuredData\DataObjects\SchemaObject;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\TextareaField;

/**
 * Creates a textarea field for providing structured data in form of YAML/JSON formatted text. Input is
 * then validated against a defined schema
 */



class StructuredDataField extends TextareaField
{
    use Injectable;
    
    protected $schemaDataType = FormField::SCHEMA_DATA_TYPE_CUSTOM;
    protected $schemaComponent = 'StructuredDataField';

    /**
     * @var $validation_schema string|SchemaObject|null
     */
    protected $validation_schema = null;
    protected ?string $validation_schema_name = null;


    /**
     * @param $name string Name of this Field
     * @param string|SchemaObject|null $validation_schema String representation of a JSON/YAML formatted schema or a reference to a SchemaObject
     * @param $schema_name string Name of this schema, not needed when using a SchemaObject as $validation_schema
     */
    public function __construct(string $name, string $validation_schema = null, string $schema_name = '')
    {
        $this->validation_schema = $validation_schema;
        $this->validation_schema_name = $schema_name;
        $this->setName($name)->setValue('');
        $this->addExtraClass('ssd_textarea');
        parent::__construct($name, null, '');
    }

    public function validate($validator): bool
    {
        if (empty($this->validation_schema)) {
            $validator->validationError(
                $this->name,
                _t(__CLASS__ . '.NO_SCHEMA', '_Schema `{schema_name}` not found', ['schema_name' => $this->validation_schema_name]),
                "validation");
            return false;
        } else {
            $error = "";
            if ($this->validation_schema instanceof SchemaObject) {
                if (!$this->validation_schema->validateObject($this->value, $error)) {
                    $validator->validationError(
                        $this->name,
                        $error,
                        "validation");
                    return false;
                }
            } else {
                if (!SchemaObject::validateAgainstSchemaString($this->value, $this->validation_schema, $error, $this->validation_schema_name)) {
                    $validator->validationError(
                        $this->name,
                        $error,
                        "validation");
                    return false;
                }
            }
        }
        return parent::validate($validator);
    }

    /**
     * Returns the string name of the validation schema
     *
     * @return string|null
     */
    public function getValidationSchemaName(): ?string
    {
        return $this->validation_schema_name;
    }

    /**
     * Sets sets the validation schema for this field.
     *
     * @param string|SchemaObject $validation_schema String representation of a JSON/YAML formatted schema or a reference to a SchemaObject
     * @param string $schema_name Name of this schema, not needed when using a SchemaObject as $validation_schema
     * @return StructuredDataField This instance
     */
    public function setValidationSchema($validation_schema, string $schema_name = ''): StructuredDataField
    {
        $this->validation_schema = $validation_schema;
        if ($validation_schema instanceof SchemaObject) {
            $this->validation_schema_name = $validation_schema->schema_name;
        } else {
            $this->validation_schema_name = $schema_name;
        }
        return $this;
    }


    public function getSchemaStateDefaults(): array
    {
        $state = parent::getSchemaStateDefaults();
        $state['data'] += [
            'validation_schema_name' => $this->getValidationSchemaName(),
            'tooltip' => _t(
                __CLASS__ . '.FIELD_TOOLTIP',
                '_Data in this field is validated against {schema_name}', ['schema_name' => $this->getValidationSchemaName()])
        ];
        return $state;
    }

}