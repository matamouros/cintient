<html>
<head>
  <title>Cintient</title>
  <script type="text/javascript" src="/js/jquery-1.4.4.js"></script>
</head>
<body style="background-color:#333;color:#fff;">
{if $_SESSION.user instanceof User}
{$_SESSION.user->getUsername()}
<br>
<a href="{URLManager::getForDashboard()}">dashboard</a> | <a href="{URLManager::getForProjectNew()}">new project</a>
{/if}