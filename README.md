# Silverstripe structured data module

Create, manage and validate structured yaml/json formatted text data and make it available through graphQL

## Installation

Install through composer: `composer require oposs/silverstripe-structured-data`.

To enable the graphQL type, add the following lines to your `app/_config/graphql.yml`:

```yaml
SilverStripe\GraphQL\Schema\Schema:
  schemas:
    default:
      src:
        - 'oposs/silverstripe-structured-data: _graphql'
```

If you just need a form field which can be validated against a JSON schema, you may want to disable the admin backend:

```yaml

Oposs\StructuredData\Extensions\StructuredDataAdmin:
  show_admin_interface: false
```

## Permissions

Global permissions provided by this module:

- `STRUCTURED_DATA_VIEW`: Ability to view the Structured Data Module and stored data and schemas
- `STRUCTURED_DATA_ADMIN`: Users holding this permission can edit/create/delete all data and schemas

Fine-grained control over editing access is possible by defining allowed groups for each data object individually. 


## Usage

Besides the possibility to access the data via graphQL this module also provides a specialized `StructuredDataField` which can be
setup to validate it's input against a schema:

```php
<?php
use Oposs\StructuredData\Form\StructuredDataField;
use Oposs\StructuredData\DataObjects\SchemaObject;
use SilverStripe\Control\Controller;

$SCHEMA_DUMMY = '{}';

TextAreaField::create('schema_field')
    ->setTitle('Schema for yaml_field');
    ->setReadonly(!Permission::check('SOME_SUPER_ADMIN_CAPABILITY'))

StructuredDataField::create('yaml_field')
    // Using a schema object
    ->setValidationSchemaName(SchemaObject::get('name')->first())
    // Using a string
    ->setValidationSchemaName($SCHEMA_DUMMY)
    // And the special case when the schema is configurable in the same form
    ->setValidationSchemaName(Controller::curr()->getRequest()->postVar('schema_field') ?? '{}')
    ->setTitle('Yaml Field');
   
```

## GraphQL example

```json
{
    "query": "{ readStructuredDatas(filter: {key: {eq: \"test-data\"}}) { nodes {  asJsonBlob   structured_data   }  }}"
}
```