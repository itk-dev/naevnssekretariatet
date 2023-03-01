# Assets

This document describes which kind of assets (Javascript, Styles, etc.)
are used in this project, which file structure we conform to and other
guidelines to consider when working with assets.

These are the types of assets fount in the project:

* JavaScript for creating interactive elements in web pages.
* Sass for styling.

## Asset management

The [built-in asset management](https://symfony.com/doc/current/frontend.html)
provided by Symfony is used. This means that the project use the Webpack Encore functionality.

### Building assets

To build the assets in the project, run the following command in a terminal:

```sh
docker compose run --rm node yarn install
docker compose run --rm node yarn dev
```

When building assets for production use

```sh
docker compose run --rm node yarn build
```

## JavaScript

JavaScript files are placed in the assets folder:

```sh
/project_root
  /assets
```

A global js file is used for JavaScript that concern all pages.
The file is called app.js and is placed in the assets folder:

```sh
/project_root
  /assets
      app.js
```

### Page specific JavaScript

We group our JavaScript files by routes. This means that if you have a route
called dashboard, and have some JavaScript that only affects the dashboard route,
you should place it in a folder in the assets directory named after the route:

```sh
/project_root
  /assets
    /dashboard
      dashboard.js
```

Folders for routes and javascript files for actions is all in lowercase.

If you need to make components, that is used in for example a route specific
JavaScript file, you should place the component in the same folder as the
main JavaScript files is placed.
The component file should start with an uppercase letter.

For example if you have a Feed component that is imported in the dashboard.js file,
the structure should look like this:

```sh
/project_root
  /assets
    /dashboard
      dashboard.js
      Feed.js
```

Remember to add new JavaScript files to the webpack config file
placed in the project root:

```js
// webpack.config.js
Encore
    // ...
    .addEntry('dashboard', './assets/dashboard/dashboard.js')
    // ...
```

And to the specific page it should affect:

```twig
{# dashboard/index.html.twig #}
{% block javascripts %}
    {{ parent() }}
    {{ encore_entry_script_tags('dashboard') }}
{% endblock %}
```

See the [Webpack Encore documentation](https://symfony.com/doc/current/frontend/encore/simple-example.html#page-specific-javascript-or-css-multiple-entries)
for more information.

## Styling

The [Sass language](https://sass-lang.com/)
is used for defining styles within our project.
Sass files are placed in the assets folder:

For example

```sh
/project_root
  /assets
```

A global scss file is used for styles that concerns all pages.
The file is called app.scss and is placed in the assets folder:

```sh
/project_root
  /assets
    app.scss
```

### Page specific styles

Sass files are placed with the JavaScript modules that use them.
For example if you have styles that is used in the dashboard/dashboard.js
file they should be placed in the dashboard folder:

```sh
# Action specific styles
/project_root
  /assets
    /dashboard
      dashboard.js
      dashboard.scss
```

Remember to import the styles in the JavaScript file and
add the styles to the specific page it should affect:

```js
// dashboard/dashboard.js
import './dashboard.scss'
```

```twig
{# dashboard.html.twig #}
{% block stylesheets %}
    {{ parent() }}
    {{ encore_entry_link_tags('dashboard') }}
{% endblock %}
```

See the [Webpack Encore documentation](https://symfony.com/doc/current/frontend/encore/simple-example.html#page-specific-javascript-or-css-multiple-entries)
for more information.

## Coding standards

See the [CONTRIBUTING.md](CONTRIBUTING.md) for information about the coding
standards we adhere to when working assets.

## Useful links

* [Managing CSS and JavaScript in Symfony](https://symfony.com/doc/current/frontend.html)
* [Page-Specific JavaScript or CSS (Multiple Entries) in Symfony](https://symfony.com/doc/current/frontend/encore/simple-example.html#page-specific-javascript-or-css-multiple-entries)
* [Sass language](https://sass-lang.com/)
