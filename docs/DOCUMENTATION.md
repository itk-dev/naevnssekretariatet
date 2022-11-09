# Documentation

All our documentation is written in the [Markdown](https://en.wikipedia.org/wiki/Markdown)
format and should adhere to the [rules](https://github.com/DavidAnson/markdownlint#rules--aliases)
described in the [markdownlint](https://github.com/DavidAnson/markdownlint)
tool we use for linting our markdown files.

Documentation files should be named in all caps with .md as the file extension
and placed in the docs folder.
For inspiration on how to write and structure documentation you could look at the
documentation files that already exist.

A very simple template could look something like this:

```markdown
# Title

Short description.

## Useful links

* [Link](http://url.extension/path)

```

## When to write documentation

Rule of thumb is that everything should be documented, but there may be some
exceptions to that rule.
Here follows some general guidelines to when writing documentation:

* If you develop a component, that other components will make use of.
* If you introduce a rule, a new coding standard, a new paradigm, or something else
  that affects all further development.

### Exceptions

* If the area you want to document, already is documented as part of a standard library,
  common sense or otherwise not meaningful to document, it may be an exception.
  If uncertain reach out to other members of the team.

## Useful links

* [Rules](https://github.com/DavidAnson/markdownlint#rules--aliases)
* [markdownlint](https://github.com/DavidAnson/markdownlint)
