{include file='includes/header.inc.tpl'}
<form action="{URLManager::getForAuthentication()}" method="post">
Username<input type="text" name="username"><br>
Password<input type="password" name="password"><br>
<input type="hidden" value="{if isset($authentication_redirectUri)}{$authentication_redirectUri}{/if}" name="redirect">
<input type="submit">
</form>
{include file='includes/footer.inc.tpl'}