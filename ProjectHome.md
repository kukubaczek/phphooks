# PHP Hooks API, a plugin system solution #
![http://phphooks.googlecode.com/files/hooks-plugins.png](http://phphooks.googlecode.com/files/hooks-plugins.png)

PHP Hooks API is a way to load the plug-in scripts through **hook** embeded in main program. This API can be used to setup and embed Plugins in main code just like how it is done in [Wordpress Plugin API](http://codex.wordpress.org/Plugin_API).
## Plugin System ##
Successful use of plugin system help you and third party developers to add additional functional modules to the main program by storing separate location(folder) instead of modifing the main code. It can freely dis/assemble these functions by add/delect Plugin folders without affecting the others.
## Plugin ##
Is a program, or a set of one or more functions, written in the codes stored in separate Plugin locations(folders), Plugin can freely add specific set of features or services to the main program. Plugin can be seamlessly integrated with the main program using access points(hooks).
## Hook ##
Hooks are provided by plugin system such as **PHP Hooks API** to allow your Plugin code to 'hook into' the main program.
## Apply Hook ##
Tell the main program, what time to execute the 'hooked in' function scripts stored in particular separate plugin folder.