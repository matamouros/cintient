Changelog
=========


2012-05-26 cintient-1.0.0
-------------------------
 *  ADDED: Revision column in the Releases tab of the Dashboard.
 *  FIXED: Some issues with paths in Linux.
 *  CHANGED: Git commit hashes are now 10 chars long everywhere.
 *  CHANGED: Minimum PHP version bumped to 5.3.3.


2012-01-12 cintient-1.0.0-RC1
-----------------------------

 *  ADDED: Every PHP executable invocation now runs with the same ini
           configuration file as the webserver, by default.
           (thanks @rasismeiro)
 *  ADDED: SCM environment vars, in case the SCM executables need to be
           fed some configuration of some sort.
 *  ADDED: Favicon support.
 *  ADDED: Cintient release version number on the database.
 *  ADDED: Automated builder now detects an upgraded installation's
           database and quits itself.
 *  FIXED: Regression on project creation. (thanks @rasismeiro)
 *  FIXED: Fatal error in the integration builder, in case a task was
           inactive.
 *  FIXED: Removal of symlinks pointing to directories now works
           properly, both internally and on the Delete task.
 *  FIXED: Available builder tasks not showing up, on Windows.
           (thanks @rasismeiro)
 *  FIXED: Several path issues, on Windows. (thanks @rasismeiro)
 *  FIXED: Problem with output of the integration builder, on Windows.
           (thanks @rasismeiro)
 *  FIXED: Issue that prevented project deletion.
 *  FIXED: Regression that broke SVN authentication. (thanks @jpfaria)
 *  FIXED: Issue with installer's logging severity level.
 *  CHANGED: Max avatar upload size increased to 200KB.
 *  CHANGED: SCM SVN connector is now more verbose on errors.
 *  CHANGED: PhpDepend task now has a default includes dir.


2011-12-31 cintient-1.0.0-beta-11
---------------------------------

 *  ADDED: Package generation for successful builds. Packages are a
           build's sources exported to an archive. For now only tar is
           supported for package generation.
 *  ADDED: Active attribute to tasks. Unactive tasks can still be edited
           and seen, but they will not be executed.
 *  FIXED: Git connector's update method on Windows platforms.
 *  FIXED: ReplaceRegexp task was wrongly categorized as a PHP task.
 *  FIXED: PhpLint task now outputs syntax error details (in the Raw
           tab of the Build history).
 *  FIXED: Broken property expansion on "bootstrap" attribute of PhpUnit
           task.
 *  FIXED: Several other fixes and improvements, both layout and core.
 *  FIXED: A few newline compatibility issues in the installer.
 *  FIXED: Double slash issue in empty URI cases, in the installer.
 *  FIXED: Rogue notices in third party logging library.
 *  FIXED: Improved Git connector's modified sources verification.
 *  CHANGED: Improved log output.
 *  CHANGED: Project build race condition now much harder to occur.
 *  CHANGED: Much improved update method for SCM connectors, now trying
             much harder before giving up, on error.
 *  CHANGED: System settings tab in the Administration area was divided
             into Global settings and Executables tabs.
 *  CHANGED: Builder properties are now unique - specifying already
             existing properties overwrites the previous ones.


2011-12-16 cintient-1.0.0-beta-10
---------------------------------

 *  ADDED: New Administration area, complete with a reverse tail to the
           application log file and the possibility to configure the
           paths of several required third party command line
           executables.
 *  FIXED: Projects would break if SCM settings were changed.


2011-12-10 cintient-1.0.0-beta-9
--------------------------------

 *  ADDED: New quality metrics trend chart, in the dashboard, and for
           projects that were built using PhpDepend task.
 *  ADDED: Serious performance improvement in the dashboard, with the
           first page load deferring the first project details load to
           an AJAX call.
 *  ADDED: New help file, from where the future help documentation will
           derive.
 *  FIXED: Broken layout bug in builder elements with textareas.
 *  CHANGED: Other minor layout improvements.


2011-12-07 cintient-1.0.0-beta-8
--------------------------------

 *  FIXED: Broken installer in Windows, on some user specified data
           dirs.
 *  FIXED: Chrome checkboxes not saving properly. (thanks @Luzifer)
 *  CHANGED: Temporarily disabled background build handler in Windows
             platforms.


2011-11-30 cintient-1.0.0-beta-7
--------------------------------

 *  FIXED: Fail on error attribute of tasks is now saved properly.
 *  FIXED: Copy and a few other tasks now properly save their fileset.


2011-11-28 cintient-1.0.0-beta-6
--------------------------------

 *  FIXED: Regression in installer completely broke clean installs.


2011-11-27 cintient-1.0.0-beta-5
--------------------------------

 *  ADDED: New build duration chart, in the dashboard.
 *  ADDED: Upgrade support in the installer now properly handles
           previous Cintient installations.
 *  ADDED: Database defragmentation at installation time, prevents it
           growing to double the size after an upgrade.
 *  ADDED: External link in the dashboard in a given build, to view a
           GitHub commit on GitHub.com, in case it is detected as
           origin. Also another quick link to view the current build in
           the project's build history.
 *  FIXED: Several issues and inconsistencies with Database logic,
           namely while handling transactions.
 *  FIXED: Minor bugs in the dashboard, for never built and recently
           created projects.
 *  FIXED: Bug in build dir backup in case of a build already existed.
 *  FIXED: Issue with build start datetime which was always the build
           end datetime instead.
 *  CHANGED: Charts formatting improvements.


2011-11-21 cintient-1.0.0-beta-4
--------------------------------
 
 *  FIXED: Serious broken logic with the project status indicators, in
           the dashboard.
 *  FIXED: Broken logic with build buttons in the dashboard, as well as
           build buttons showing up without regard for current user's
           permissions (or lack).


2011-11-20 cintient-1.0.0-beta-3
--------------------------------

 *  ADDED: Revisions in the project's build history section are loaded
           via AJAX, instead of demanding a full page reload.
 *  ADDED: Support for specifying a bootstrap file in PhpUnit tasks.
 *  ADDED: New Failed status for projects, to help distinguish pure
           Error states from Failed builds.
 *  FIXED: Wrong SQLite version being checked, at install time. (thanks
           @franzliedke)
 *  FIXED: Charts problems with hidden data indicators until a page
           refresh happened.
 *  FIXED: Dashboard's Latest tab now shows the status of a project's
           latest build, not the current project status.
 *  FIXED: Flaw in datetime arithmetic for polling project's SCM.
 *  CHANGED: Builders are now further solidified, now showing whatever
             unflushed output remains at the end of their execution and,
             in that condition, assuming an error occurred.
 *  CHANGED: Increased project SCM changes polling to 10 minutes.
 *  CHANGED: Improvements on the reliability of the Git SCM connector.


2011-11-16 cintient-1.0.0-beta-2
--------------------------------
 
 *  ADDED: Manual building trigger buttons in the Dashboard, for on
           demand builds.
 *  FIXED: Several performance issues.
 *  FIXED: Serious SQLite database locking issues.
 *  FIXED: Minor layout issues.
 *  FIXED: Regression in database handling of some queries.
 *  FIXED: Issue in the PhpUnit task implementation would cause the
           integration builder to fail silently if the PHP environment
           was different from the web server to the console (namely if
           one had Xdebug loaded and the other didn't).


2011-11-10 cintient-1.0.0-beta-1
--------------------------------
 
 *  CHANGED: Major UI refactoring.
 *  CHANGED: Several other improvements and fixes.


2011-10-02 cintient-0-alpha-8
-----------------------------
 
 *  FIXED: Major bugfixing and improvements.
 *  ADDED: Tooltips to some forms. Still working on improving everything
           with more information.
 *  ADDED: ReplaceRegex task.
 *  ADDED: Configuration file now generated at install time.
 *  CHANGED: Delete task now activated.


2011-09-21 cintient-0-alpha-7
-----------------------------

 *  ADDED: Git support as an SCM connector. Still very dodgy and
           alpha-ish.
 *  FIXED: Some minor bugs squashed.


2011-09-13 cintient-0-alpha-6
-----------------------------
 
 *  FIXED: LOTS of bugs squashed and performance issues (some quite
           SERIOUS) fixed, either in the backend and frontend.
 *  FIXED: Serious Notification design flaw caused only the logged on
           user to receive notifications, instead of all project
           registered users.


2011-09-05 cintient-0-alpha-5
-----------------------------

 *  ADDED: Notifo.com user account notifications, as well as an
           extendable architecture for new notification handlers. Users
           can configure available notification handlers and can
           configure per project notifications for specific
           predetermined events.
 *  FIXED: Installer is now compatible with PHP 4 and PHP 5.2.
 *  FIXED: Integration builder issue would report success on certain
           cases where tasks would generate a fatal error.
 *  FIXED: Several minor tweaks and improvements.

  
2011-08-19 cintient-0-alpha-4
-----------------------------

 *  FIXED: URL handling issues bug squashing.
