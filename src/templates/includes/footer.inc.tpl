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
    <div class="paragraph"><a href="http://code.google.com/p/cintient/">Cintient</a>, Continuous Integration made simple, is free software distributed under the New BSD License terms.</div>
    <div class="paragraph">Copyright &copy; 2011, <a href="mailto:pedro.matamouros@gmail.com">Pedro Mata-Mouros</a>. All rights reserved.</div>
  </div>
</body>
</html>