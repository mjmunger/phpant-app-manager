# Working with CSS
By default, your app will try to load any *.css files location in this directory.

You can prevent this by commenting out the following line in the file header:

`* App Action: header-inject-css -> injectCSS              @ 50`

Change the ":" to a "#" to disable the loads:

`* App Action# header-inject-css -> injectCSS              @ 50`

If you want to control the order in which the CSS is loaded, name your files using numeric values such as:

```
01-loadfirst.css
02-loadsecond.css
03-loadlast.css
```