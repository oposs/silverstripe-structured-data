<?php

namespace Oposs\StructuredData\Form;

use Oposs\StructuredData\DataObjects\SchemaObject;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\TextareaField;

/**
 * Creates a textarea field for providing structured data in form of YAML/JSON formatted text. Input is
 * then validated against a defined schema
 */
class StructuredDataField extends TextareaField
{

    protected $schemaDataType = FormField::SCHEMA_DATA_TYPE_CUSTOM;
    protected $schemaComponent = 'StructuredDataField';

    /**
     * @var string|null
     */
    protected ?string $validation_schema = null;


    public function __construct($name, string $validation_schema = null, $title = null)
    {
        $this->validation_schema = $validation_schema;
        $this->setName($name)->setValue('');
        $this->addExtraClass('ssd_textarea');
        parent::__construct($name, null, '');
    }

    public function validate($validator): bool
    {
        /**
         * @var SchemaObject $schema
         */
        $schema = SchemaObject::get()->filter(['schema_name' => $this->validation_schema])->first();
        if (empty($schema)) {
            $validator->validationError(
                $this->name,
                _t(__CLASS__ . '.NO_SCHEMA', '_Schema `{schema_name}` not found', ['schema_name' => $this->validation_schema]),
                "validation");
            return false;
        }
        $error = "";
        if (!$schema->validateObject($this->value, $error)) {
            $validator->validationError(
                $this->name,
                $error,
                "validation");
            return false;
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
        return $this->validation_schema;
    }

    /**
     * Sets sets the validation schema to schema_name.
     *
     * Note: The Schema itself has be created first in the Structured Data Admin
     *
     * @param string $validation_schema A valid schema name
     * @return StructuredDataField This instance
     */
    public function setValidationSchemaName(string $validation_schema): StructuredDataField
    {
        $this->validation_schema = $validation_schema;
        return $this;
    }


    public function getSchemaStateDefaults(): array
    {
        $state = parent::getSchemaStateDefaults();
        $state['data'] += [
            'validation_schema' => $this->getValidationSchemaName(),
            'tooltip' => _t(
                __CLASS__ . '.FIELD_TOOLTIP',
                '_Data in this field is validated against {schema_name}', ['schema_name' => $this->getValidationSchemaName()])
        ];
        return $state;
    }

}