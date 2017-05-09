# Usage – Link Registry Bundle

1. [Installation](01-installation.md)
2. [Configuration](02-config.md)
3. [**Usage**](03-usage.md)


## In project files

Here is the example how to use the registry in the project source files:

```php
$linkRegistry = $container->get('cfg_link_registry');

if ($linkRegistry->hasEntry('app_login')) {
    $entry = $linkRegistry->getEntry('app_login');
    
    // Get the URL
    $url = $entry->getUrl();
    
    // Get the title
    $title = $entry->getTitle();
    
    // Get the page model
    if ($entry->isInternal()) {
        $page = $entry->getPageModel();
    }
}
```


## Insert tags

You can reference the links using insert tags as follows:

```
{{cfg_link_registry::url::app_login}} - returns URL to the `app_login` page
{{cfg_link_registry::href::app_login}} - returns href (ampersand URL) to the app_login page
{{cfg_link_registry::title::app_login}} - returns title of the link
```
