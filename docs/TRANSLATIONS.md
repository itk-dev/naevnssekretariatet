# Translations

We write our translations in the XLIFF format. The format
is widely used for writing translations, and there are
many tools that supports this format.

We group our translations by domains. This breaks down our translation
files to more manageable files instead of one large file.

When we do our translations, we use words or sentences as keys. This
means we describe what we translate instead of where we are translating.

English sentences or words are used as keys as default, with danish translations

Translation files are generated automatically after the translations are
used in Twig templates and code.

We use the [ICU Message
Format”](https://symfony.com/doc/current/translation/message_format.html) for
translations and this means that [message
placeholders](https://symfony.com/doc/current/translation/message_format.html#message-placeholders)
*must be wrapped* in `{}` and in the text and *not wrapped* when passing
parameters to the translation, i.e.

```twig
{{ 'No results for {searchTerm}'|trans({'searchTerm': search}) }}
```

```twig
{% trans with {'searchTerm': search} %}No results for {searchTerm}{% endtrans %}
```

```php
$translator->trans('No results for {searchTerm}', ['searchTerm' => $search])
```

## Workflow

1. Use translation in Twig templates:

   ```twig
   {# Set the default translation domain for the Twig template #}
   {% trans_default_domain 'users' %}

   {% trans %}Change password{% endtrans %}
   ```

2. Or in code

   ```php
   // Remember to set the domain as the third parameter.
   $translator->trans('Change password', [], 'users');
   ```

3. Generate/update the translation files:

   ```sh
   docker-compose exec -e DEFAULT_LOCALE=en phpfpm bin/console translation:extract --force da
   # Mark default translations as “Needs work”.
   sed -i '' 's/\<target\>__/\<target state="needs-l10n"\>__/' translations/*.xlf
   ```

4. Remember to clear the cache after the translations have been updated:

   ```sh
   docker-compose exec phpfpm bin/console cache:clear
   ```

## Editors

* [POEdit](https://poedit.net/)

## Useful links

* [Symfony Translations](https://symfony.com/doc/current/translation.html)
