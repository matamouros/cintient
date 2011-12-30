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
subSectionTitle="Administration"
subSectionId="admin"
subSectionDescription=""
jsIncludes=['js/lib/bootstrap/bootstrap-tabs.js']}
    <ul class="tabs">
      <li class="active"><a href="#log">Log</a></li>
      <li><a href="#settings">Global settings</a></li>
    </ul>

    <div class="tab-content">

      <div class="active" id="log">
        <p>Last refresh was at <span id="dateLastRefresh">...</span></p> <a href="{UrlManager::getForAdmin()}" id="btnLogRefresh" class="btn primary">Refresh</a> <div class="loading" style="display: none;"><img src="imgs/loading-3.gif" /></div>
        <div class="log"></div>
      </div>

      <div id="settings">
        <form action class="form" id="settingsForm">
          <fieldset>
{$globals_settings->getView()}
            <div class="actions">
              <input type="submit" class="btn primary" value="Save changes">&nbsp;<button type="reset" class="btn">Cancel</button>
            </div>
          </fieldset>
        </form>
      </div>

    </div>

<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  Cintient.initSectionAdmin();
  Cintient.initGenericForm({
    submitUrl : '{UrlManager::getForAjaxAdminSettings()}'
  });

  $('#btnLogRefresh').click(function (e) {
    e.preventDefault();
    $('#btnLogRefresh')
      .removeClass('primary')
      .addClass('disabled')
      .text('Please wait...');
    $('.loading').fadeIn(300);

    $.ajax({
      url: '{UrlManager::getForAjaxAdminLog()}',
      type: 'GET',
      dataType: 'html',
      success: function(data, textStatus, XMLHttpRequest) {
        // Following condition according to jQuery's .load() method
        // documentation:
        // http://api.jquery.com/load/
        if (textStatus == 'success' || textStatus == 'notmodified') {
          $('.log')
            .hide()
            .html(data); // Update the HTML (replace it)
          $('.log').fadeIn(300); // Show it all
        } else {
          Cintient.alertUnknown();
        }
        $('.loading').fadeOut(100);
        $('#btnLogRefresh')
          .removeClass('disabled')
          .addClass('primary')
          .text('Refresh');
        date = new Date();
        $('#dateLastRefresh').html(date.toTimeString());
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        Cintient.alertUnknown();
        $('.loading').fadeOut(100);
        $('#btnLogRefresh')
          .removeClass('disabled')
          .addClass('primary')
          .text('Refresh');
      }
    });
  });

  $('#btnLogRefresh').trigger('click'); // First load
});
// ]]>
</script>
{include file='includes/footer.inc.tpl'}