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

*}
  </div>
  <div id="footer" class="containerTopLevel">
    {if !isset($onlyLicense)}{TemplateManager::providerInstallationStats()}{$buildsTotal={Project::getCountTotalBuilds()}}<div id="installationStats">Sentient since {$providerInstallationStats_installDate|date_format}. Monitoring {$providerInstallationStats_projectsCount} project{if $providerInstallationStats_projectsCount != 1}s{/if}, with {$providerInstallationStats_usersCount} user{if $providerInstallationStats_usersCount != 1}s{/if}, built {$buildsTotal} time{if $buildsTotal != 1}s{/if}.</div>{/if}
    <div class="paragraph">Cintient is free software distributed under the GNU General Public License version 3 or later terms.</div>
    <div class="paragraph">Copyright &copy; 2010, 2011, Pedro Mata-Mouros Fonseca. All rights reserved.</div>
  </div>
<script type="text/javascript">
// <![CDATA[
$('#footer').hide();
$(document).ready(function() {
  $('#footer').fadeIn(200);
  // Setup tooltips
  $(function(){
    $('.tooltip').tipTip();
  });
});
// ]]>
</script>
</body>
</html>