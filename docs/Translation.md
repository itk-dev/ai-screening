# Translation

This document describes how [we](https://github.com/itk-dev/) handle translations in Drupal projects to make it possible
to add new translations and make sure that we can update translations in development, staging and production
envirenment.

## Introduction

We have four types of translations in Drupal:

1. [Custom module and theme translations](#custom-module-and-theme-translations)
2. [Config translations](#config-translations)
3. [Contrib translations](#contrib-translations)

The following section will define these translation types in more detail.

> [!IMPORTANT]
> We _don't_ use the `interface translation` stuff defined in
> [locale.api.php](https://git.drupalcode.org/project/drupal/-/blob/11.x/core/modules/locale/locale.api.php), but
> control exactly what we export to where and how we import again from said where.

## Custom module and theme translations

"Custom module translations" and "custom theme translations" are translations used in custom
modules and themes, i.e. translatable strings used in our own module code and templates.

> [!NOTE]
> For brevity, we'll use the term "custom module" to cover both custom modules and custom themes in the following
> sections.

We use [Translation template extractor](https://www.drupal.org/project/potx) to extract translations from custom
modules. See [Taskfile.translation.yml](../task/Taskfile.translation.yml) for details.

When [Exporting translations](#exporting-translations) for a custom module, `my_module`, say, a `translations` folder is
added in the module folder and inside this folder a [translation file](https://en.wikipedia.org/wiki/Gettext) is added
for each translation target language, e.g.

``` shell
web/modules/custom/my_module
└── translations
    ├── my_module.da.po
    ├── ⋮
    └── my_module.no.po
```

> [!IMPORTANT]
> We don't add [translation
> contexts](https://www.drupal.org/docs/8/api/translation-api/overview#s-context-in-drupalt-and-drupalformatplural) to
> module translations, so the same source translation may exist in translation file for multiple modules. If this
> becomes an issue, an explicit context can be added to a translation (where it's used).

## Config translations

"Config translations" are translations of config.

We use [Config Translation PO](https://www.drupal.org/project/config_translation_po) to export config translations. This
module currently [does not have an Drush commands](https://www.drupal.org/project/config_translation_po/issues/3439416)
for exporting and importing translations, but we're working on adding these:
<https://git.drupalcode.org/issue/config_translation_po-3439416/-/compare/1.0.x...3439416-added-drush-command>.

All config translations are exported to a single file per language, e.g. `config/translations/config.da.po` for the
languge `da`.

> [!NOTE]
> If the translation of a config item text is the same as the source text, the translation text will be empty. This
> makes it easy to identify missing translations.

## Contrib translations

"Contrib translations" are all other translations, i.e. translations used in Drupal core and contrib modules.

We use [`drush locale:export`](https://www.drush.org/latest/commands/locale_export/) to export contrib translations and
we export only customized translations, i.e. we use a Drush command like

``` shell
drush locale:export da --types=customized
```

to export contrib translations for the languge `da`.

Contrib translations are exported to a single file per languge, e.g. `translations/contrib-translations.da.po`.

> [!IMPORTANT]
> Contrib translations may include module and config translations, so care must be taken when [importing
> translations](#importing-translations).

-------------------------------------------------------------------------------

## Exporting translations

In your main [Taskfile](https://taskfile.dev/getting-started/), include
[Taskfile.translation.yml](../task/Taskfile.translation.yml) and [set the
variables](https://taskfile.dev/usage/#vars-of-included-taskfiles) `TRANSLATION_MODULES`, `TRANSLATION_THEMES`, and
`TRANSLATION_LANGUAGES` to define modules and themse to export translations for and the [languages to
process](https://en.wikipedia.org/wiki/ISO_639-1):

``` yaml
# taskfile.yml

includes:
  translation:
    taskfile: ./task/Taskfile.translation.yml
    vars:
      TRANSLATION_MODULES:
        - my_module
        ⋮

      TRANSLATION_THEMES:
        - my_theme
        ⋮

      TRANSLATION_LANGUAGES:
        - da
        ⋮
```

Export all types of translations by running

``` shell name=translation-export
task translation:export
```

> [!NOTE]
> When exporting translations, only "customized" contrib translations are exported.

During development and testing, you can use specific export tasks for the different translation types:

``` shell
> task
…
* translation:contrib-translations:export:       Export contrib translations for all languages
* translation:config-translations:export:        Export config translation for all languages
* translation:module-translations:export:        Export translations for all modules in all languages
* translation:theme-translations:export:         Export translations for all themes in all languages
…
```

Run `task translation:translations:list` to list all translation files, e.g.

``` shell
> task translation:translations:list
… translations/contrib-translations.da.po
… config/translations/config.da.po
… web/modules/custom/my_module/translations/my_module.da.po
… web/themes/custom/my_theme_reports/translations/my_theme_reports.da.po
```

## Importing translations

All translations can be imported by running

``` shell name=translation-import
task translation:import
```

When importing translations, new translations are imported as "not-customized" and only "not-customized" translations
are overridden, and This ensures that any user made changes a translation will not be overridden.

> [!WARNING]
> As noted above, contrib translations include all modules translations and therefore the contrib translations must be
> imported first when importing translations. Otherwise, translations from module translations files may be overridden
> by contrib translations.
>
> This may actually not be an issue (since we only override "not-customized" translations), but care should be taken
> when importing translations.

During development and testing, you can use specific import tasks for the different translation types:

``` shell
> task
…
* translation:contrib-translations:import:       Import contrib translations for all languages
* translation:config-translations:import:        Import config translation for all languages
* translation:module-translations:import:        Import translations for all modules in all languages
* translation:theme-translations:import:         Import translations for all themes in all languages
…
```
