TWCMS
------------

You are free to do with this code as you please.
I care too little about licenses to bother adding one,
so rest assured I don't care what you do with it.

Currently only Apache is supported (htaccess)

Basic Usage
-------------

Start by editing settings in *config.inc.php*

All pages are setup as clean URLs in the content directory.
To create a new page, you simply create a file with the url you want.

For example, the URL /about/ simply looks for the file */content/about.html*.
Content files can also be dynamic, using the name *about.inc.php*.
These two file types, HTML and PHP, have different formats which are explained
below. The HTML file type is given preference over PHP, if both types are
present.

**URL Structure**: There are important characters in the TWCMS file structure. If
your page has a space in it, like "About Us", the corresponding url would be
about-us (*about-us.html*). However, if you wish to create a subpage such as
/about/history/, the file would be *about_history.html*. Underscores are used for
subpages only. The number of subpage "levels" is unlimited. These subpage
levels are automatically added to breadcrumbs.

Additional URL symbols include ".", and "~", which don't affect the file
structure, but do effect breadcrumb title translations.
See *lib/processing.inc.php*:p_url2name for more.

**HTML Format**: Only used for simple static pages. The format is extremely
simple; the first line of the file becomes the Header and Page Title in the
template, the rest of the file becomes the content.
**IMPORTANT:** All HTML on the first line is stripped from Header/Title.

Sample HTML Format:
```html
About Us
<p>Learn all about our Website!</p>
```

**PHP Format**: Used for any page that requires dynamic content. This tends to be
the most common type. Using the PHP format simply requires knowing about
3 "template variables". A template variable is just a value in the $T global
array. The 3 important ones are: title, header, and content. In 99% of use cases,
the Title and Header are the same. The difference is Title goes in the HTML
`<title>`, and the Header becomes the main H2 title (also used in breadcrumbs).
The content variable is simply the HTML displayed in the content area.

Sample PHP Format:

```php
<?php
$T['title'] = $T['header'] = 'About Us';
$T['content'] = '<p>Learn all about our Website!</p>';
?>
```

Note on PHP Formats:
A PHP page can stop execution simply by calling 'exit', which would prevent
the main template from displaying. This is useful if the page needs to use a
different template, or perhaps return non-templated content, such as
during AJAX calls.

Templating
-------------

All templating is done inside *index.php*
This file simply handles the different template variables. TWCMS
does not support multiple templates by default, but it would be
trivial to include different template files based on a parameter or setting.

**Resource Files (CSS & JS)**: Resource files are unique in TWCMS, as they are
designed to be included by default. If you have page-specific CSS you
want to add to a page, rather than bloating your global CSS file, simply create
a file in the "css" directory with the name of your page.

For example: The page /about/history/ has some custom styling. All I have to do
s create a file called */css/cms.about_history.css*. This file is automatically
included on this page only. The format for JS files is the same.

In addition to these page-specific resources, TWCMS has 3 pre-defined files:
global, index, and subpage. As you might expect, global is included on every
page, index is included only on the homepage, and subpage is included on every
page EXCEPT for the homepage. This allows for easy organizing of your template
code without bloating your files.

TWCMS auto adds timestamps to resources, to prevent caching issues.


Modules
-------------

Modules are loaded from the "mods" directory. Each module has two files:
.inc.php and .cfg.php. The .inc includes all the function calls for this
module, and the .cfg includes all the configuration options. Both files are
automatically loaded if the module is enabled in the global config.inc.php file
(See mods_enabled).

**Events**: Events are called with `tw_event(<event>)`, and allow exchange of
information or updates across multiple modules. Each time an event is fired,
the system goes through each loaded module looking for that function name. For
example, on page load the even 'onLoad' is automatically called. So each module
has <mod>_onLoad called, if such a function exists. Lots of events are used
throughout TWCMS. To learn more, search for tw_event calls in the code and get
more detail from the source comments.
