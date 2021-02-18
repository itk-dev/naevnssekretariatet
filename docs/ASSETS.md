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
docker run -v ${PWD}:/app itkdev/yarn:latest encore dev
```

When building assets for production use, omit the dev option:

```sh
docker run -v ${PWD}:/app itkdev/yarn:latest encore
```

## JavaScript

JavaScript files are placed in the assets/js folder:

```sh
/project_root
  /assets
    /js
```

A global js file is used for JavaScript that concern all pages.
The file is called app.js and is placed in the assets/js folder:

```sh
/project_root
  /assets
    /js
      app.js
```

### Page specific JavaScript

There should only be one JavaScript file for a page,
and it should be named after the page itself.
For example if you have a Dashboard page,
the corresponding JavaScript file should be named dashboard.js.

If you need to split up your JavaScript for a page,
place the sub JavaScript in a folder in the assets/js named
after the main JavaScript file but starting with an underscore:

```sh
/project_root
  /assets
    /js
      /dashboard
        _partial.js
      dashboard.js
```

Require the partial in the main JavaScript file:

```javascript
// dashboard.js
require('dashboard/_partial.js')
```

Remember to add new JavaScript files to the webpack config file
placed in the project root:

```js
// webpack.config.js
Encore
    // ...
    .addEntry('app', './assets/dashboard.js')
    // ...
```

And to the specific page it should affect:

```twig
{# dashboard.html.twig #}
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
Files are placed in the assets/styles folder:

```sh
/project_root
  /assets
    /styles
```

A global scss file is used for styles that concerns all pages.
The file is called app.scss and is placed in the assets/styles folder:

```sh
/project_root
  /assets
    /styles
      app.scss
```

### Page specific styles

As with JavaScript files, there should only be one Sass file for a page.
It should be named after the page it affects.
For example if you have a Dashboard page, the Sass file should be named dashboard.scss.

If you need to split up your Sass files, place your partials in a subfolder
within the assets/styles folder named after the page it concerns,
but starting with an underscore:

```sh
/project_root
  /assets
    /styles
      /dashboard
        _partial.scss
      dashboard.scss
```

Import the partial in the main page specific Sass file:

```sass
/* dashboard.scss */
@import 'dashboard/_partial.scss'; 
```

Remember to add the styles to the specific page it should affect:

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
