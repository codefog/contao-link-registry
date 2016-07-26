Link Registry extension for Contao Open Source CMS
==================================================

The extension provides a backend interface for managing particular website links in one place. It is especially
useful if you have a common page that needs to be linked from various places of your app.

For example, the login page can be referenced from various places. Instead of creating multiple references
(either ```{{link_*}}``` insert tags or ```jumpTo``` pages) you simply define it in one place and then refer to it
using the link registry.

Installation
------------

Run the composer command to install the package:

```
composer require codefog/contao-link-registry
```

You also have to register the bundle in your ```AppKernel``` class:

```php
$bundles = [
    // ...
    new Codefog\LinkRegistryBundle\CodefogLinkRegistryBundle(),
    // ...
];

```

Finally run the Contao install tool to update the database.

Configuration
-------------

Setup your desired page types in ```app/config/config.yml``` file:

```yaml
# Codefog Link Registry configuration
codefog_link_registry:
    types: ["app_login", "app_logout", "app_profile"]
```

They should also be labelled in ```languages/en/tl_cfg_link_registry.php``` file:
 
```php
/**
 * Link registry entry types
 */
$GLOBALS['TL_LANG']['tl_cfg_link_registry']['types'] = [
    'app_login'   => ['Login page', 'Please choose the page with login module on it.'],
    'app_logout'  => ['Logout page', 'Please choose the page with logout module on it.'],
    'app_profile' => ['Member profile page', 'Please choose the page with member profile module on it.'],
];
```

Backend setup
-------------

In the backend first you have to create a registry in the ```Layout > Link registries``` module. Then in its settings
assign pages to the certain link types. You can assign internal pages as well as external URLs.

Once your first registry is ready assign it in the website root settings so it's available on the subpages.

Usage
-----

You can reference the links using insert tags as follows:

```
{{cfg_link_registry::url::app_login}} - returns URL to the app_login page
{{cfg_link_registry::link::app_login}} - (same as above)
{{cfg_link_registry::href::app_login}} - returns href (ampersand URL) to the app_login page
{{cfg_link_registry::title::app_login}} - returns title of the link
```

Or using the service in container:

```php
$linkRegistry = $this->container->get('codefog_link_registry.registry');

if ($linkRegistry->hasEntry('app_profile')) {
    return $linkRegistry->getLink('app_profile');
}
```