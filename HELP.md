Cintient - Continuous Integration made simple.
==============================================

Introduction
============

What is Cintient
----------------
Cintient is a Continuous Integration self-hosted web application written
in PHP. It was written to be simple to setup and straightforward to use
- with special attention to UI - in order to bring CI practices to most
small to medium scale projects that just don't justify the setup of a
more complex CI solution.

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
 *  PHP syntax checking task
 *  PHPUnit integration for unit testing
 *  PHPDepend for quality metrics
 *  PHPCodeSniffer for coding standards enforcement
 *  Code coverage support (PHP Xdebug extension required)
 *  Perl syntax check (just so you know you can add support to pretty
    much every language you want)

Requirements
------------

 *  Apache HTTPD with mod_rewrite (for the automatic installation)
 *  PHP 5.3.0 or later
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


Build history - /project/history
================================

As soon as your project's first ever build is finished, you can check
its details in the Build history. On the far right side of the page
header you have a dropdown menu from where you can choose other builds.
You also have a quick at-a-glance status indicator for each of these
past builds. All the build details in the Build history correspond to
the currently selected build in the dropdown. 

Raw output
----------
This tab shows you the raw console output of the integration builder.
Every task prints out its type into every output line, i.e., "[phplint]"
for PhpLint tasks, etc.

This tab is always present in the Build history, and cannot be removed.
You should use this as the first line of troubleshooting for any
problems with your project builds.

Special tasks
-------------
Seamlessly integrated with normal tasks, special tasks can trigger
changes to Cintient's interface. One example of this is the PhpDepend
special task, that extends the project's Build history with a new tab
called "Quality". Each of these new tabs contain specific information
provided by the execution of their corresponding special tasks. Check
this documentation for details on what special tasks exist, on how to
configure them, how to understand their results, and also on how to
extend Cintient in order to provide your own special tasks.

### Quality
The Quality tab is introduced by the PhpDepend special task. PhpDepend
analyses your project's PHP source code and extracts several metrics 
(taken from PHP_Depend documentation - by Manuel Pichler, available at
http://pdepend.org/documentation/ - and freely adapted):

#### AHH - Available Hierarchy Height
The maximum lenght average from a given class to its deepest subclass,
i.e., the average number of classes chaining from the parent down to its
deepest descendant.

#### ANDC - Average Number of Derived Classes
A class' average number of direct subclasses (direct descendants).

#### Calls
The number of method/function calls.

#### CCN - Cyclomatic Complexity Number
It is a complexity indicator of a specific software fragment, calculated 
by the number of available decision paths in that fragment. These
decision paths are started by very common control structures in PHP:

 *  ?
 *  case
 *  elseif
 *  for
 *  foreach
 *  if
 *  while
 
The higher the CCN, the more complex a given software fragment is.
Very often the complex parts of an application contain business critical
logic. But this complexity has negative impacts on the readability and
understandability of the source code. Those parts will normally become
a maintenance and bug fixing nightmare, because no one knows all the
constraints, side effects and what's exactly going on in that part of
the software. This situation results in the well known saying "never
touch a running system". The situation can even become more critical
when the original author leaves the development team or the company.

 *  A software fragment with a CCN value between 1-4 has low complexity.
 *  A complexity value between 5-7 is moderate and still easy to
    understand.
 *  Everything between 6-10 has a high complexity.
 *  Everything greater 10 is very complex and hard to understand.

#### CCN2 - Extended Cyclomatic Complexity Number
The CCN indicator does not take into account every single decision path,
like || and && operators. CCN2 already covers these and a few more.

 *  ?
 *  &&
 *  ||
 *  or
 *  and
 *  xor
 *  case
 *  catch
 *  elseif
 *  for
 *  foreach
 *  if
 *  while

#### CLOC - Comment Lines Of Code
The number of lines of code comments.

#### CLSA - Number of Abstract Classes
The number of abstract classes.

#### CLSC - Number of Concrete Classes
The number of concrete classes.

#### Fanout, CBO (Coupling Between Objects) or CE (Efferent Coupling)
The number of referenced classes within a given class.
An indicator of the coupling factor of a given class, the CBO is a
function of the unique number of references that occur in an object
through method calls, method parameters, return types, thrown exceptions
and accessed fields - with the exception of subclasses or parent
classes, which do not affect the CBO value.

Excessive coupled classes hinder reuse of existing components and
are suboptimal for a modular, encapsulated software design. To improve
the modularity the coupling between different classes should be kept to
a minimum. Besides less reusability, a high level of coupling has a
second drawback: a class that is highly coupled to other classes is more
sensitive to changes in those classes and as a result it becomes more
difficult to maintain and gets more error-prone. Additionally it is
harder to test a heavly coupled class in isolation and it is harder to
understand such a class. Therefore, although code reuse is still always
highly desirable, the number of dependencies between classes should
always be kept at the very minimum possible.

#### LLOC - Logical Lines Of Code
The number of actual logical lines of code. A logical line of code,
i.e., a single statement of code, can often span over several physical
lines of code in the name of readability or even coding style. An
interpreter or compiler will actually see source code as a set of
logical lines of code, not physical ones.

#### LOC - Lines Of Code
The physical number of lines of code. Physical lines of code can contain
multiple logical lines of code.

#### NOC - Number Of Classes
The number of classes.

#### NOF - Number Of Functions
The number of functions.

#### NOI - Number Of Interfaces
The number of interfaces.

#### NOM - Number Of Methods
The number of methods.

#### NOP - Number Of Packages
The number of packages.
