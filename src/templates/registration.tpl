{*
    Cintient, Continuous Integration made simple.
    Copyright (c) 2010, 2011, Pedro Mata-Mouros Fonseca
    
    This file is part of Cintient.
    
    Cintient is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    
    Cintient is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with Cintient. If not, see <http://www.gnu.org/licenses/>.

*}{include file='includes/header.inc.tpl'
  subSectionTitle="Registration"}
    <div class="registrationContainer container">
      <form action="{URLManager::getForRegistration()}" method="post">
      <ul class="item">
        <li class="element">
          <div class="label">Name</div>
          <div class="textfieldContainer" style="width: 306px;"><input class="textfield" style="width: 300px;" type="text" class="mandatory" name="name" value="{if isset($formData['name'])}{$formData['name']}{/if}" /></div>
        </li>
        <li class="element">
          <div class="label">Email</div>
          <div class="textfieldContainer" style="width: 406px;"><input class="textfield" style="width: 400px;" type="email" class="mandatory" name="email" value="{if isset($formData['email'])}{$formData['email']}{/if}" /></div>
        </li>
        <li class="element">
          <div class="label">Username</div>
          <div class="textfieldContainer" style="width: 256px;"><input class="textfield" style="width: 250px;" type="text" class="mandatory" name="username" value="{if isset($formData['username'])}{$formData['username']}{/if}" /></div>
        </li>
        <li class="element">
          <div class="label">Password</div>
          <div class="textfieldContainer" style="width: 206px;"><input class="textfield" style="width: 200px;" type="password" class="mandatory" name="password" value="" /></div>
        </li>
        <li class="element">
          <div class="label">Repeat Password</div>
          <div class="textfieldContainer" style="width: 206px;"><input class="textfield" style="width: 200px;" type="password" class="mandatory" name="password2" value="" /></div>
        </li>
        <input id="submitButton" type="submit" value="Go!" />
      </ul>
      </form>
    </div>
{include file='includes/footer.inc.tpl'}