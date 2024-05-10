# starter-statamic

## TODO

-   [ ] More fieldsets
-   [ ] Blueprints / collections
-   [ ] Icons in `icons` container/filesystem
-   [ ] Create some customization commands like deleting certain collections etc that someone might not use?
-   [ ] Also use this repo to create a custom `{{ img }}` tag or any other addon.
-   [ ] SEO config instead of an addon (or make our own simple / just the basics addon or include the first party one)
-   [ ] Analytics config
-   [ ] When Tailwind update comes out with CSS config, potentially allow doing that in the CP as well?
-   [ ] Nav review / implementations: children in the ui, site wide primary call to action config in both nav/index and nav/mobile_menu, logo from site config
-   [ ] Deploy script, NGINX configuration, steps to deploy like env file config etc.
-   [ ] Github action to export starter kit and also addons?

## Users

Ships with an admin user already created: `admin@example.com` `Password123!`

## Docker

A `docker-compose.yml` file and custom `Dockerfile` are included. `docker-compose up -d` should get you started. Visit `localhost:8080`.

## spatie/laravel-ray

The starter kit ships with Ray installed. It is configured by default to work with Docker. `SEND_EXCEPTIONS_TO_RAY` is set to false.

## Config

-   `config/statamic/cp.php` Theme is set to `business`. `getting_started` widget has been commented out.
-   `config/filesystems.php` A `site` filesystem is set up by default. This can be used for static assets that the end-user should not be able to manipulate. e.g. a folder of icons or images for pages that you have not created a blueprint for the user to adjust.
-   `config/mail.php` Postmark message stream is set up to default to outbound but allows adjusting via an env var.
-   `config/statamic/git.php` If git is set up, git commit messages are prefixed with `[BOT]`. This allows you to skip deployments on content changes in production.
-   `config/statamic/static_caching.php` If using static caching, query strings are ignored. This helps prevent bot traffic creating many static files (e.g. my-site.com/?randomquery=123). If any pages rely on query parameters, you'll have to either exclude those pages from static caching or use something like the `{{ nocache }}` tag.

## Fields

### Common

#### Image

A field to use for image selection. Applies image validation to the Asset field type so the user cannot upload something like a PDF or Word document, which can cause errors.

## Considerations

-   Adjusting the CMS name and branding in `config/statamic/cp.php`.
-   Running `npm remove @fontsource-variable/inter`, deleting the import from `resources/js/site.js` and using your own.

## Recommended Extensions

Ships with recommended extensions for Tailwind, Prettier, and Antlers.

## JavaScript Packages

Ships with AlpineJS and a sample font (Inter).
