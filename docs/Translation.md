# Translation

We use [Translation template extractor](https://www.drupal.org/project/potx) to extract translations from custom modules
and themes.

We use the `interface translation` stuff defined in
[locale.api.php](https://git.drupalcode.org/project/drupal/-/blob/11.x/core/modules/locale/locale.api.php).

Extract translations by running

``` shell
task translation:translations:extract
```

Import (extracted) translations by running

``` shell
task translation:translations:import:all
```

## Config translation

We use [Config Translation PO](https://www.drupal.org/project/config_translation_po) to export config translations. This
module currently [does not have an Drush commands](https://www.drupal.org/project/config_translation_po/issues/3439416)
for exporting and importing translations, but we're working on adding these:
<https://git.drupalcode.org/issue/config_translation_po-3439416/-/compare/1.0.x...3439416-added-drush-command>.

Export config translations to [config/translations.da.po](./config/translations.da.po) by running

``` shell
task translation:config-translations:export
```

Import config translations (from [config/translations.da.po](./config/translations.da.po)) by running

``` shell
task translation:config-translations:import
```
