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

*}{TemplateManager::providerFooter()}
  </div>
  <div id="footer" class="containerTopLevel">
    <div id="installationStats">Sentient since {$providerFooter_installDate|date_format}. Monitoring {$providerFooter_projectsCount} projects, with {$providerFooter_usersCount} user, built {Project::getCountTotalBuilds()} times.</div>
    <div class="paragraph">Cintient is free software distributed under the GNU General Public License version 3 or later terms.</div>
    <div class="paragraph">Copyright &copy; 2010, 2011, Pedro Mata-Mouros Fonseca. All rights reserved.</div>
  </div>
</body>
</html>