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

JavaScript files is grouped by routes and named after the action it concerns.
For example if you have a Dashboard route, with an index action, the structure
should look like this:

```sh
/project_root
  /assets
    /dashboard
      index.js
```

Folders for routes and javascript files for actions is all in lowercase.

If you need to make components, that is used in an action JavaScript file,
you should place the component in the same folder as the action JavaScript
files is placed. The component file should start with an uppercase letter.

For example if you have a Feed component that is imported in the index.js file,
the structure should look like this:

```sh
/project_root
  /assets
    /dashboard
      index.js
      Feed.js
```

Remember to add new JavaScript files to the webpack config file
placed in the project root:

```js
// webpack.config.js
Encore
    // ...
    .addEntry('dashboard_index', './assets/dashboard/index.js')
    // ...
```

And to the specific page it should affect:

```twig
{# dashboard/index.html.twig #}
{% block javascripts %}
    {{ parent() }}
    {{ encore_entry_script_tags('dashboard_index') }}
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
