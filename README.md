[![License](https://poser.pugx.org/trompette/feature-toggles/license)](LICENSE)
[![Stable Version](https://poser.pugx.org/trompette/feature-toggles/v/stable)](https://packagist.org/packages/trompette/feature-toggles)
[![Automated Tests](https://github.com/trompette/php-feature-toggles/actions/workflows/automated-tests.yml/badge.svg)](https://github.com/trompette/php-feature-toggles/actions/workflows/automated-tests.yml)

# trompette/feature-toggles

This PHP library implements a feature toggle infrastructure.
 
Using `trompette/feature-toggles` library can help a team to deliver new
features to users iteratively and safely, in other words: it enables continuous
deployment.

For more information on the topic, see [*Feature Toggles (aka Feature Flags)* on
MartinFowler.com](https://martinfowler.com/articles/feature-toggles.html).

## Installation

The `trompette/feature-toggles` library is distributed on
[Packagist](https://packagist.org/packages/trompette/feature-toggles).

It can be added as a project dependency with the following command:

```bash
composer require trompette/feature-toggles
```

## Standalone usage

When working on a new version of a page, deploying gradually the new version can
bring a lot of confidence to a team.

But it also brings more work, as the team needs to:
- keep the code implementing the current version,
- add the code implementing the new version,
- and consistently enable the new version for some users.

With `trompette/feature-toggles` library, enabling the new version is done by
asking the [toggle router](sources/ToggleRouter.php) if the current user has a
feature:

```php
if ($toggleRouter->hasFeature($currentUser, 'new_page_version')) {
    $templating->render('new_page.tpl', $newParameters);
} else {
    $templating->render('page.tpl', $parameters);
}
```

### Feature registry

Before using the [toggle router](sources/ToggleRouter.php), `new_page_version`
feature has to be registered:

```php
use Trompette\FeatureToggles\FeatureDefinition;
use Trompette\FeatureToggles\FeatureRegistry;

$featureRegistry = new FeatureRegistry();
$featureRegistry->register(new FeatureDefinition(
    $name = 'new_page_version',
    $description = 'awesome new version of a page',
    $strategy = 'whitelist'
));
```

### Toggling strategies

When defining a feature, a [toggling strategy](sources/TogglingStrategy.php) has
to be referenced to specify the algorithm deciding if a target has a feature.

Implemented strategies are:
- feature is enabled for all targets or disabled for all targets, see
[`OnOff`](sources/OnOffStrategy/OnOff.php) class,
- feature is enabled for whitelisted targets only, see
[`Whitelist`](sources/WhitelistStrategy/Whitelist.php) class,
- feature is enabled for a percentage of all targets, see
[`Percentage`](sources/PercentageStrategy/Percentage.php) class.

And strategies can be combined with boolean operators, like so:
`onoff and whitelist`, `onoff or whitelist or percentage`, etc.

### Toggle router 

Now that the [feature registry](sources/FeatureRegistry.php) is configured, the
[toggle router](sources/ToggleRouter.php) can be created:

```php
use Doctrine\DBAL\Connection;
use Trompette\FeatureToggles\DBAL\WhitelistStrategyConfigurationRepository;
use Trompette\FeatureToggles\ToggleRouter;
use Trompette\FeatureToggles\WhitelistStrategy\Whitelist;

$connection = new Connection(...);
$repository = new WhitelistStrategyConfigurationRepository($connection);
$whitelist = new Whitelist($repository);

$toggleRouter = new ToggleRouter(
    $featureRegistry,
    $strategies = ['whitelist' => $whitelist]
);
```

Strategies are injected as an array indexed with names: these are the references
that should be used when registering features.   

### Feature configuration

The [toggle router](sources/ToggleRouter.php) can be used to configure a feature
for a given strategy:

```php
$toggleRouter->configureFeature('feature', 'onoff', 'on');
$toggleRouter->configureFeature('feature', 'onoff', 'off');

$toggleRouter->configureFeature('feature', 'whitelist', 'allow', 'target');
$toggleRouter->configureFeature('feature', 'whitelist', 'disallow', 'target');

$toggleRouter->configureFeature('feature', 'percentage', 'slide', 25);
$toggleRouter->configureFeature('feature', 'percentage', 'slide', 50);
```

Configuration changes are persisted by calling the associated method on the
strategy instance.

All Doctrine DBAL configuration repositories can migrate a schema, since they
all implement the [`SchemaMigrator`](sources/DBAL/SchemaMigrator.php) interface:

```php
use Doctrine\DBAL\Connection;
use Trompette\FeatureToggles\DBAL\WhitelistStrategyConfigurationRepository;

$connection = new Connection(...);
$repository = new WhitelistStrategyConfigurationRepository($connection);
$repository->migrateSchema();
```

## Usage with Symfony

All previous code is optional when using Symfony: everything is glued together
by the [`FeatureTogglesBundle`](sources/Bundle/FeatureTogglesBundle.php) class.

Registering the [bundle](sources/Bundle/FeatureTogglesBundle.php) in
`config/bundles.php` is needed to benefit from the Symfony integration:

```php
return [
    // ...
    Trompette\FeatureToggles\Bundle\FeatureTogglesBundle::class => ['all' => true],
];
```

### Bundle configuration

The [bundle](sources/Bundle/FeatureTogglesBundle.php) can be configured as
described by `config:dump-reference`:
  
```yaml
# Default configuration for extension with alias: "feature_toggles"
feature_toggles:
    doctrine_dbal_connection: doctrine.dbal.default_connection
    declared_features:

        # Prototype
        name:
            description:          ~ # Required
            strategy:             ~ # Required
```

For technical details, see
[`FeatureTogglesConfiguration`](sources/Bundle/FeatureTogglesConfiguration.php)
class.
 
### Container services

There is only one service declared as public: the [toggle
router](sources/ToggleRouter.php) with `Trompette\FeatureToggles\ToggleRouter`
as identifier.

There are also useful console commands defined as services and tagged with
`console.command`:

```
 feature-toggles
  feature-toggles:configure-feature           Configures a feature
  feature-toggles:migrate-dbal-schema         Migrates DBAL schema
  feature-toggles:show-feature-configuration  Shows a feature configuration
```

For technical details, see
[`FeatureTogglesExtension`](sources/Bundle/FeatureTogglesExtension.php) class.

## License

The `trompette/feature-toggles` library is released under the MIT License.

See the [LICENSE](LICENSE) file for more details.
   
## Acknowledgments

The `trompette/feature-toggles` library is inspired by a practice and a tool
used by the [Food Assembly development team](https://github.com/lrqdo).

The team discovered the practice with the article [*Web Experimentation with New
Visitors* on Etsy's Engineering
Blog](https://codeascraft.com/2014/04/03/web-experimentation-with-new-visitors).
