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
   docker-compose exec -e DEFAULT_LOCALE=en phpfpm bin/console \
   translation:update --force da
   ```

4. Remember to clear the cache after the translations have been updated:

   ```sh
   docker-compose exec phpfpm bin/console cache:clear
   ```

## Editors

* [POEdit](https://poedit.net/)

## Useful links

* [Symfony Translations](https://symfony.com/doc/current/translation.html)
