# JsonApiBundle

[![Join the chat at https://gitter.im/steffenbrem/JsonApiBundle](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/steffenbrem/JsonApiBundle?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Integration of JSON API with Symfony 2 (FOSRestBundle)

> Note that this is stil a WIP and should not be used for production!

## Usage
> Coming soon

If you want to experiment with this implementation, you can just enable this bundle in your `AppKernel` and everything should work directly. Try to serialize some annotated php classes and check it out!

### Configuration reference
```yml
mango_json_api:
    show_version_info: true # default
    base_uri: /api # default
```

## Annotations
### @Resource
This will define your class as a JSON-API resource, and you can optionally set it's type name.
> This annotation can be defined on a class.

```php
use Mango\Bundle\JsonApiBundle\Configuration\Annotation as JsonApi;

/**
 * @JsonApi\Resource(type="posts", showLinkSelf=true)
 */
 class Post 
 {
  // ...
 }
```
| Property      | Default | Required  | Content   | Info  |
| ---           | ---     | ---       | ---       | ---   |
| type          | ~       | No        | string    | If left default, it will resolve its type dynamically based on the short class name. |
| showLinkSelf  | true    | No        | boolean   | Add `self` link to the resource |

### @Id (optional, it defaults to `id`)
This will define the property that will be used as the `id` of a resource. It needs to be unique for every resource of the same type.
> This annotation can be defined on a property.

```php
use Mango\Bundle\JsonApiBundle\Configuration\Annotation as JsonApi;

/**
 * @JsonApi\Resource(type="posts")
 */
 class Post 
 {
    /**
     * @JsonApi\Id
     */
    protected $uuid;
 }
```

### @Relationship
This will define a relationship that can be either a `oneToMany` or `manyToOne`. Optionally you can set `includeByDefault` to include (sideload) the relationship with it's primary resource.
> This annotation can be defined on a property.

```php
use Mango\Bundle\JsonApiBundle\Configuration\Annotation as JsonApi;

/**
 * @JsonApi\Resource(type="posts")
 */
 class Post 
 {
    // ..
    
    /**
     * @JsonApi\Relationship(includeByDefault=true, showLinkSelf=false, showLinkRelated=false)
     */
    protected $comments;
 }
```
| Property              | Default | Required  | Content   | Info  |
| ---                   | ---     | ---       | ---       | ---   |
| includeByDefault      | false   | No        | boolean   | This will include (sideload) the relationship with it's primary resource |
| showData              | false   | No        | boolean   | Shows `data`, which consists of ids of the relationship data |
| showLinkSelf          | false   | No        | boolean   | Add `self` link of the relationship |
| showLinkRelated       | false   | No        | boolean   | Add `related` link of the relationship |

## Configuration Reference
```yaml
# app/config/config.yml

mango_json_api:
    show_version_info: true
```

## Example response
> GET /api/channels

```json
{
    "jsonapi": {
        "version": "1.0"
    },
    "meta": {
        "page": 1,
        "limit": 10,
        "pages": 1,
        "total": 4
    },
    "data": [
        {
            "type": "channels",
            "id": 5,
            "attributes": {
                "code": "WEB-UK",
                "name": "UK Webstore",
                "description": null,
                "url": "localhost",
                "color": "Blue",
                "enabled": true,
                "created-at": "2015-07-16T12:11:50+0000",
                "updated-at": "2015-07-16T12:11:50+0000",
                "locales": [],
                "currencies": [],
                "payment-methods": [],
                "shipping-methods": [],
                "taxonomies": []
            },
            "relationships": {
                "workspace": {
                    "data": {
                        "type": "workspaces",
                        "id": 18
                    }
                }
            }
        },
        {
            "type": "channels",
            "id": 6,
            "attributes": {
                "code": "WEB-NL",
                "name": "Dutch Webstore",
                "description": null,
                "url": null,
                "color": "Orange",
                "enabled": true,
                "created-at": "2015-07-16T12:11:50+0000",
                "updated-at": "2015-07-16T12:11:50+0000",
                "locales": [],
                "currencies": [],
                "payment-methods": [],
                "shipping-methods": [],
                "taxonomies": []
            },
            "relationships": {
                "workspace": {
                    "data": {
                        "type": "workspaces",
                        "id": 18
                    }
                }
            }
        },
        {
            "type": "channels",
            "id": 7,
            "attributes": {
                "code": "WEB-US",
                "name": "United States Webstore",
                "description": null,
                "url": null,
                "color": "Orange",
                "enabled": true,
                "created-at": "2015-07-16T12:11:50+0000",
                "updated-at": "2015-07-16T12:11:50+0000",
                "locales": [],
                "currencies": [],
                "payment-methods": [],
                "shipping-methods": [],
                "taxonomies": []
            },
            "relationships": {
                "workspace": {
                    "data": {
                        "type": "workspaces",
                        "id": 18
                    }
                }
            }
        },
        {
            "type": "channels",
            "id": 8,
            "attributes": {
                "code": "MOBILE",
                "name": "Mobile Store",
                "description": null,
                "url": null,
                "color": "Orange",
                "enabled": true,
                "created-at": "2015-07-16T12:11:50+0000",
                "updated-at": "2015-07-16T12:11:50+0000",
                "locales": [],
                "currencies": [],
                "payment-methods": [],
                "shipping-methods": [],
                "taxonomies": []
            },
            "relationships": {
                "workspace": {
                    "data": {
                        "type": "workspaces",
                        "id": 18
                    }
                }
            }
        }
    ],
    "included": [
        {
            "type": "workspaces",
            "id": 18,
            "attributes": {
                "name": "First Workspace"
            },
            "relationships": {
                "channels": {
                    "links": {
                        "related": "/workspaces/18/channels"
                    }
                }
            }
        }
    ]
}
```
