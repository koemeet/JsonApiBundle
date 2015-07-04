# JsonApiBundle

[![Join the chat at https://gitter.im/steffenbrem/JsonApiBundle](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/steffenbrem/JsonApiBundle?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Integration of JSON API with Symfony 2 (FOSRestBundle)

> Note that this is stil a WIP and should not be used for production!

## Usage
> Coming soon

If you want to experiment with this implementation, you can just enable this bundle in your `AppKernel` and everything should directly. Try to serialize some annotated php classes and check it out!

## Annotations
### @Resource
This will define your class as a JSON-API resource, and you can optionally set it's type name.
> This annotation can be defined on a class.

```php
use Mango\Bundle\JsonApiBundle\Configuration\Annotation as JsonApi;

/**
 * @JsonApi\Resource(type="posts")
 */
 class Post 
 {
  // ...
 }
```
| Property  | Required  | Content   | Info  |
| ---       | ---       | ---       | ---   |
| type      | No        | string    | If not present, it will use the dasherized classname as it's type |

### @Id
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
    protected $id;
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
     * @JsonApi\Relationship(includeByDefault=true)
     */
    protected $comments;
 }
```
| Property  | Required  | Content   | Info  |
| ---       | ---       | ---       | ---   |
| includeByDefault      | No        | bool    | This will include (sideload) the relationship with it's primary resource |
