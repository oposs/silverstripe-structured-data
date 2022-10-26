<?php

namespace Oposs\StructuredData\DataObjects;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationResult;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;


class YamlSchema extends DataObject
{

    private static string $table_name = "YamlSchema";
    private static string $singular_name = 'Yaml Schema';

    private static array $db = [
        'schema_name' => 'Varchar(10)',
        'data' => 'Text'
    ];


    public function validate(): ValidationResult
    {
        $result = new ValidationResult();
        return $result;
    }


    public function validateData(string $data, string &$error): bool
    {
        try {
            $yaml_data = Yaml::parse($data);
        } catch (ParseException $exception) {
            $error = _t(__CLASS__ . '.YAML_PARSER_ERROR', 'Yaml parser error: {yaml_error}', ["yaml_error" => $exception->getMessage()],);
            return false;
        }
        return true;
    }

}