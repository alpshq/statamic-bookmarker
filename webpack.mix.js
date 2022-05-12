const mix = require('laravel-mix');

mix
  .js('resources/js/bookmarker.js', 'dist/js').vue()
  .postCss('resources/css/bookmarker.pcss', 'dist/css', [
    require('postcss-nested'),
  ])
  .copyDirectory('dist', '../../public/vendor/statamic-bookmarker')
  .sourceMaps()
  .disableNotifications();
