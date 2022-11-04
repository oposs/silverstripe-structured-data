# Silverstripe structured data module

Create, manage and validate structured yaml/json formatted text data and make it available through graphQL

## Installation

Install through composer: `composer require oposs/silverstripe-structured-data`.

To enable the graphQL type add the following to your `app/_config/graphql.yml`:

```yaml
SilverStripe\GraphQL\Schema\Schema:
  schemas:
    default:
      src:
        - 'oposs/silverstripe-structured-data: _graphql'
```

## Permissions

Global permissions provided by this module:

- `STRUCTURED_DATA_VIEW`: Ability to view the Structured Data Module and stored data and schemas
- `STRUCTURED_DATA_ADMIN`: Users holding this permission can edit/create/delete all data and schemas

Fine-grained control over editing access is possible by defining allowed groups for each data object individually. 


## Usage

Besides the possibility to access the data via graphQL this model also provides a specialized `StructuredDataField` which can be
setup to validate against a schema:

```php
use Oposs\StructuredData\Form\StructuredDataField;

StructuredDataField::create('yaml field')
    ->setValidationSchemaName('example')
    ->setTitle('Yaml Field)

```

## GraphQL example

```json
{
    "query": "{ readStructuredDatas(filter: {key: {eq: \"test-data\"}}) { nodes {  asJsonBlob   structured_data   }  }}"
}
```