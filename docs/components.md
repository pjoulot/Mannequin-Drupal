---
title: Components
description: Reference information for Drupal Twig components and metadata.
---
Templates are files that are used to generate HTML markup.  From Mannequin's perspective, a template becomes a component when:
1.  You tell a Mannequin where to find it using `.mannequin.php`.
2.  It is enriched with YAML metadata describing the component.

Here is an example metadata block that describes a component called "Button" that lives in `button.html.twig`.

```twig
{# button.html.twig #}
{# @Component
name: Button
description: This button can be used in...
samples:
  Large:
    classes: ['large']
    text: I am a Large button
  Small:
    classes: ['small']
    text: I am a small button
#}
<a class="button {{ classes|join(' ') }}">
    {{text}}
</a>
```
<div class="note">
You must always add the @Component annotation to your Twig comments to have them picked up by Mannequin.
</div>

## name
Specifies the display name of the component wherever it's shown in Mannequin.
```twig
{# @Component
name: Button
#}
```

## description
Specifies the long text description for the component. This is used to provide additional information about it in the Mannequin UI.
```twig
{# @Component
description: This button can be used in...
#}
```

## group
Specifies where the component appears in the Mannequin UI. You can use whatever groupings you like - Mannequin does not require any organization system. You can also specify a nested group using the `>` character.
```twig
{# @Component
group: Atoms
# or... 
group: Molecules>Containers
```
## samples
Samples are named sets of variables you can view in the Mannequin UI.  Samples provide examples of how the component will look in the real world.  Samples are named.  Eg: the following example has two samples, "Small", and "Large."
```twig
{# @Component
... 
samples:
    Small:
      classes: ['small']
    Large:
      classes: ['large']
#}
<div class="{{ classes|join(' ') }}">
  ... 
</div>
```
The variables that samples contain are passed to your Twig templates verbatim.  They can be simple, like strings and integers, or they can be complex, as in the above example where the `classes` variable is an array. Finally, sample variables can be dynamic using [expressions](expressions.md)

### Default Variables 
 The following default are provided with default values for every template.  They can be overridden by specifying them in a sample declaration:
 * `attributes`: An empty `Attribute` object.
 * `title_attributes`: An empty `Attribute` object.
 * `title_prefix`: An empty array
 * `title_suffix`: An empty array.
 * `db_is_active`: true
 * `is_admin`: false
 * `logged_in`: false
 
### Render Arrays
Mannequin does not support Drupal's Render system.  However, it does support rendering arrays by concatenating the values.  For example, see the following simplified `node.html.twig` template:
```twig
{# @Component
name: Node
samples:
  Article:
    content:
      field_image: ~markup('<img src="http://placehold.it/350/350" />')
      field_body: ~markup('<p>Lorem ipsum...</p>')
#}
<article>
  <div class="content">
    {{ content }}
  </div>
</div>
```
In this example, the `content` variable will concatenate the value of field_image and field_body, which is pretty close to what Drupal's render system does.  This is particularly powerful when combined with the [sample](expressions.md#sample) expression to render values from your actual field templates.

