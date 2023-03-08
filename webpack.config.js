const Encore = require('@symfony/webpack-encore')

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore
// directory where compiled assets will be stored
  .setOutputPath('public/build/')
// public path used by the web server to access the output path
  .setPublicPath('/build')
// only needed for CDN's or sub-directory deploy
// .setManifestKeyPrefix('build/')

/*
         * ENTRY CONFIG
         *
         * Each entry will result in one JavaScript file (e.g. app.js)
         * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
         */
  .addEntry('app', './assets/app.js')
  .addEntry('case_new', './assets/case/new.js')
  .addEntry('case_show', './assets/case/show.js')
  .addEntry('hearing_edit_post', './assets/hearing/edit_post.js')
  .addEntry('agenda_item_new', './assets/agenda_item/new.js')
  .addEntry('agenda_filter', './assets/agenda/processFilter.js')
  .addEntry('ajax-forms', './assets/ajax-forms.js')
  .addEntry('municipality_select', './assets/municipality/select.js')
  .addEntry('dawa-address-lookup', './assets/dawa-address-lookup.js')
  .addEntry('identification-lookup', './assets/case/identification-lookup.js')
  .addEntry('identification_type_handler', './assets/identification/type_handler.js')
  .addEntry('admin_board_member_cpr_lookup', './assets/admin/board_member_cpr_lookup.js')
  .addEntry('admin_party_identifier_lookup', './assets/admin/party_identifier_lookup.js')
  .addEntry('party_identification_lookup', './assets/party/identifier-lookup.js')
  .addEntry('agenda_mark_all_documents', './assets/agenda/mark_all_documents.js')
  .addEntry('case_new_prepare', './assets/case/municipality_and_board_selector.js')
  .addEntry('mail_templates_custom_data', './assets/mail_templates/custom_data.js')
  .addEntry('document_case_event', './assets/document/case_event.js')
  .addEntry('select2', './assets/select2.js')

  .addStyleEntry('address_protection', './assets/case/address-protection.scss')

// enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
//  .enableStimulusBridge('./assets/controllers.json')

// When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

// will require an extra script tag for runtime.js
// but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

/*
         * FEATURE CONFIG
         *
         * Enable & configure other features below. For a full
         * list of features, see:
         * https://symfony.com/doc/current/frontend.html#adding-more-features
         */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
// enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  .configureBabel((config) => {
    config.plugins.push('@babel/plugin-proposal-class-properties')
  })

// enables @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage'
    config.corejs = 3
  })

// enables Sass/SCSS support
  .enableSassLoader()

// uncomment if you use TypeScript
// .enableTypeScriptLoader()

// uncomment if you use React
// .enableReactPreset()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
// .enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
  .autoProvidejQuery()

// copy images from assets to build https://symfony.com/doc/current/frontend/encore/copy-files.html
  .copyFiles({
    from: './assets/images'
  })

module.exports = Encore.getWebpackConfig()
