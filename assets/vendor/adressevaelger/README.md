# Adressevaelger

Replacement for `dawa-autocomplete2`, which depends on the DAWA service shut
down 2026-08-17.

* Upstream: <https://github.com/SDFIdk/adressevaelger>
* Docs / migration guide: <https://github.com/Klimadatastyrelsen/adressevaelger>
* Default API URL: `https://adressevaelger.dk` (overridable via `ADRESSEVAELGER_API_URL`)
* Token: obtain via [SDFI Brugerstyring](https://confluence.sdfi.dk/display/ADV/Brugerstyring), set `ADRESSEVAELGER_TOKEN` in `.env.local`.

There is no npm package, hence these files are committed directly.
When updating, replace `adressevaelger.esm.js` and `adressevaelger.css` with the
contents of the upstream `dist/` directory.
