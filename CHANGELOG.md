Changelog
=========

2011-11-20 cintient-1.0.0-beta-3
--------------------------------

 *  ADDED: Revisions in the project's build history section are loaded
           via AJAX, instead of demanding a full page reload.
 *  ADDED: Support for specifying a bootstrap file in PhpUnit tasks.
 *  ADDED: New Failed status for projects, to help distinguish pure
           Error states from Failed builds.
 *  FIXED: Wrong SQLite version being checked, at install time. (Thanks
           lie2815)
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
