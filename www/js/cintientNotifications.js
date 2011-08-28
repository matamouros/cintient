/*
 * 
 *  Cintient, Continuous Integration made simple.
 *  Copyright (c) 2010, 2011, Pedro Mata-Mouros Fonseca
 *
 *  This file is part of Cintient.
 *
 *  Cintient is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Cintient is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Cintient. If not, see <http://www.gnu.org/licenses/>.
 *  
 */

/**
 * This is Cintient's notifications JS helper class.
 * 
 * @author Pedro Mata-Mouros <pedro.matamouros@gmail.com>
 */

var CintientNotifications = function (options)
{
  this.init(options);
};

CintientNotifications.prototype = {
  /**
   * 
   */
  init: function (options)
  {
    //
    // Create the submit button
    //
    var button = cintient.createButton('Save');
    button.className = 'buttonText';
    button.style.display = 'none';
    //
    // Bind the click action
    //
    $(button).click(function () {
      var data = {};
      $('#notificationsPane .projectEditContainer').each(function () {
        var that = this;
        data[$(this).attr('id')] = function() {
          var x = {};
          $('input', that).each( function() {
            x[this.name] = { type: this.type, value: this.value };
          });
          $('textarea', that).each( function() {
            x[this.name] = { type: this.type, value: this.value };
          });
          return x;
        }();
      });
      $.ajax({
        url: options.submitUrl,
        data: data,
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, XMLHttpRequest) {
          if (!data.success) {
            //TODO: treat this properly
            alert('error');
          } else {
            //$(that).fadeOut(300);
            $.jGrowl("Saved.");
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          alert(errorThrown);
        }
      });
    });
    //
    // Get it into the DOM tree and show it!
    //
    $("#notificationsPane").append(button);
    setTimeout(function() {$(button).fadeIn(800)}, 400);
  }
};