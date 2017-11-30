# Autoloading Javascript

## Summary

PHP-Ant apps are designed to _only_ load javascript libraries needed for the immediate task at hand. Individual apps load their own javascript libraries and are responsible for bringing their own resources along with them. Javascript libraries that are needed site wide (discouraged unless you really do need them on _every_ page) should be loaded in the theme app to make them universally available. Otherwise, load only what you will use to keep the page size small and load times low.

This directory contains two folders:
* footer
* header

These correspond to load locations in a default theme template that implements the `header-inject-js` and `footer-inject-js` actions.

For the most part, you will put large libraries (jQuery) in the header, but all other functionality scripts will be put in the footer directory, which will (should) inject the javascript just below the `</body>` tag.

## Autoloading by default

By default, your app will try to load *.js files froom the js/footer and `js/header` directories into the footer and header, respectively. If you are not using javascript for your app (like an API, for example), you should remark out or remove the following app actions:

` * App Action: footer-inject-js  -> injectFooterJS         @ 50`

` * App Action: header-inject-js  -> injectHeaderJS         @ 50`

As always, any change to app actions requires you re-publish the app to update the manifest files.

## Controling load / execution order

Like the CSS autoloader, you can control the loading order by naming the files numerically:

```
01-loadfirst.js
02-loadsecond.js
03-loadlast.js
```
