<?php

namespace Oposs\StructuredData\Form;

use Oposs\StructuredData\DataObjects\YamlSchema;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlField extends TextareaField
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
        parent::__construct($name, null, '');
    }

    public function validate($validator): bool
    {
        /**
         * @var YamlSchema $schema
         */
        $schema = YamlSchema::get()->filter(['key' => $this->validation_schema])->first();
        if (empty($schema)) {
            $validator->validationError(
                $this->name,
                _t(__CLASS__ . '.YAML_NO_SCHEMA', 'Yaml schema `{schema_name}` not found', ['schema_name' => $this->validation_schema]),
                "validation");
            return false;
        }
        $error = "";
        if (!$schema->validateData($this->value, $error)) {
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
     */
    public function setValidationSchema(string $validation_schema): YamlField
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
                'Yaml data in this field is validated against {schema_name}', ['schema_name' => $this->getValidationSchema()])
        ];
        return $state;
    }

}