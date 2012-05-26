Cintient - Continuous Integration made simple.
==============================================

Cintient is a Continuous Integration self-hosted web application written
in PHP. It was written to be simple to setup and straightforward to use
- with special attention to UI - in order to bring CI practices to most
small to medium projects that just don't justify the setup of a more
complex CI solution.

### Screenshot #1
![Dashboard](http://c6.quickcachr.fotos.sapo.pt/i/o0a07b51c/9487527_vZqls.png)

### Screenshot #2
![History](https://img.skitch.com/20111114-dcqk93pm7uxnhgqus5gr8utd4y.png)

### Screenshot #3
![Builder](https://img.skitch.com/20111114-kp5edxr8e3umukuc2srkji6nfw.png)

What it does
------------
Cintient works by keeping track of all commits going into a source code
repository. Every time a change in the source code of a project is
detected, something called an "integration builder" is automatically
run. This integration builder can be completely configured to do
whatever the user wants - ranging from simple code syntax checks, to
more complex software quality analysis. The results are collected and
shown in a variety of charts that you can use to inspect your projects.
With such automation you can have every single commit be thoroughly
checked for syntax errors, be unit tested, inspected for violations to
your adopted coding standard, etc., all without requiring human
intervention. Whenever any of these checks fail, the build fails, and
Cintient promptly push-notifies users of such. Below are some of
Cintient's features:

 *  easy one-time setup of the application (1 minute)
 *  visual edition for builder tasks, i.e., no messing around with Ant
    XML sources for configuring builds
 *  Web-based push notifications (currently supports the now deprecated
    Notifo.com, but it's easily extendable to others)
 *  PHP syntax checking tasks
 *  PHPUnit integration for unit testing
 *  PHPDepend for quality metrics
 *  PHPCodeSniffer for coding standards enforcement
 *  Code coverage support (PHP Xdebug extension required)
 *  Perl syntax check (just so you know you can add support to pretty
    much every language you want)
    
Disclaimer
----------
Cintient is still beta. Please understand that not everything is yet
perfect. You should keep in mind the following:

 *  I develop mainly in Mac OS X and Linux, using Safari and ocasionally
    Firefox. Other OS/browser flavours are still pretty much untested.
    Please help out if you can.
 *  Setting up a project in Cintient is way simpler than in any other
    CI server I know of. But it still requires some minimum effort on
    your part to properly configure it. Don't give up, and let me know
    if you're having problems with anything.

Requirements
------------
 *  Apache HTTPD with mod_rewrite (for the automatic installation)
 *  PHP 5.3.3 or later
 *  PHP with sqlite3 version 3.3.0 or later
 *  [Optional] Xdebug (for PHP code coverage support)

Installing
----------
Check the requirements above. You need them in order to perform an easy
install. Go through the following steps:

 1.  Your web server's configuration must allow you to specify per
     directory .htaccess files and mod_rewrite directives within them.
     For this you need to have at least "AllowOverride FileInfo". Also,
     enable "Options FollowSymLinks" and "DirectoryIndex index.php". If
     you've changed these just now, don't forget to restart your Apache
     server. 
 2.  Open up a browser window, navigate to the directory where you
     unpacked Cintient. You should see the installer coming up.
 3.  Follow the instructions. It should only take you a minute to
     finish.

Contributing
------------
Willing to contribute? Awesome! Fork the project in GitHub and send some
pull requests our way.

Credits
-------
Authored by:

 *  Pedro Mata-Mouros,
    Twitter: @matamouros,
    email: pedro.matamouros@gmail.com

Past contributors:

 *  rasismeiro
 *  voxmachina
 *  miguellmanso

Project page: <http://github.com/matamouros/cintient>
