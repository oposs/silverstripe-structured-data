# Silverstripe structured data module

Create, manage and validate structured yaml/json formatted text data

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

## GraphQL example

```json
{
    "query": "{\n  readStructuredDatas(filter: {key: {eq: \"test-data\"}}) {\n    nodes {\n      asJsonBlob\n      structured_data\n    }\n  }\n}\n"
}
```