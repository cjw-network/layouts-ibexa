Netgen Layouts & eZ Platform integration installation instructions
==================================================================

Use Composer to install the integration
---------------------------------------

Run the following command to install Netgen Layouts & eZ Platform integration:

```
composer require netgen/layouts-ezplatform
```

Activating integration bundles
------------------------------

After completing standard Netgen Layouts install instructions, you also need to
activate `NetgenEzPublishBlockManagerBundle` and `NetgenContentBrowserEzPlatformBundle`.
Make sure they are activated after all other Netgen Layouts and Content Browser bundles.

```
...

$bundles[] = new Netgen\Bundle\BlockManagerAdminBundle\NetgenBlockManagerAdminBundle();
$bundles[] = new Netgen\Bundle\EzPublishBlockManagerBundle\NetgenEzPublishBlockManagerBundle();
$bundles[] = new Netgen\Bundle\ContentBrowserEzPlatformBundle\NetgenContentBrowserEzPlatformBundle();

return $bundles;
```

Activating legacy eZ Publish extension
--------------------------------------

If you use eZ Platform legacy admin interface in your eZ Platform installation,
you might want to activate `nglayouts` legacy extension to be able to add
`nglayouts/admin` and `nglayouts/editor` policies to your roles.

Add the following to your legacy `site.ini.append.php` to activate the
extension:

```
[ExtensionSettings]
ActiveExtensions[]=nglayouts
```
