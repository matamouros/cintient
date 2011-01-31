{*
  Cintient, Continuous Integration made simple.
  
  Copyright (c) 2011, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
  All rights reserved.
  
  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions
  are met:
  
  . Redistributions of source code must retain the above copyright
    notice, this list of conditions and the following disclaimer.
  
  . Redistributions in binary form must reproduce the above
    copyright notice, this list of conditions and the following
    disclaimer in the documentation and/or other materials provided
    with the distribution.
    
  . Neither the name of Pedro Mata-Mouros Fonseca, Cintient, nor
    the names of its contributors may be used to endorse or promote
    products derived from this software without specific prior
    written permission.
    
  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
  FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
  COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
  INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
  BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
  CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
  LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
  ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
  POSSIBILITY OF SUCH DAMAGE.
    
*}{include file='includes/header.inc.tpl'}
    <form action="{URLManager::getForAuthentication()}" method="post">
    <div id="loginContainer" class="container">
      <div class="loginLabel">Username</div>
      <div class="loginTextfieldContainer">
        <input class="loginTextfield" type="text" name="username" />
      </div>
      <div class="loginLabel">Password</div>
      <div class="loginTextfieldContainer">
        <input class="loginTextfield" type="password" name="password" />
      </div>
      <input type="hidden" value="{if isset($authentication_redirectUri)}{$authentication_redirectUri}{/if}" name="redirect">
      <input id="loginSubmitButton" type="submit" value="Go!" />
      {if $globals_settings[SystemSettings::ALLOW_USER_REGISTRATION]}<a href="{URLManager::getForRegistration()}" class="optionLink">register</a>{/if}
    </div>
    </form>
<script type="text/javascript">
// <![CDATA[
$('#loginContainer').hide();
$(document).ready(function() {
  $('#loginContainer').fadeIn(300);
});
// ]]> 
</script>
{include file='includes/footer.inc.tpl'}