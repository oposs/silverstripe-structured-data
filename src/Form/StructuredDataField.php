<?php

namespace Oposs\StructuredData\Form;

use Oposs\StructuredData\DataObjects\SchemaObject;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\TextareaField;

class StructuredDataField extends TextareaField
{

    protected $schemaDataType = FormField::SCHEMA_DATA_TYPE_CUSTOM;
    protected $schemaComponent = 'YamlField';

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
        $schema = SchemaObject::get()->filter(['key' => $this->validation_schema])->first();
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
     * @return string|null
     */
    public function getValidationSchema(): ?string
    {
        return $this->validation_schema;
    }

    /**
     * @param string $validation_schema
     * @return StructuredDataField
     */
    public function setValidationSchema(string $validation_schema): StructuredDataField
    {
        $this->validation_schema = $validation_schema;
        return $this;
    }


    public function getSchemaStateDefaults(): array
    {
        $state = parent::getSchemaStateDefaults();
        $state['data'] += [
            'validation_schema' => $this->getValidationSchema(),
            'tooltip' => _t(
                __CLASS__ . '.FIELD_TOOLTIP',
                '_Data in this field is validated against {schema_name}', ['schema_name' => $this->getValidationSchema()])
        ];
        return $state;
    }

}