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
 * This is Cintient's installer helper class.
 * 
 * @author Pedro Mata-Mouros <pedro.matamouros@gmail.com>
 */
var CintientInstaller = {
    
  upgrade: false,
    
  init: function ()
  {
    this.curStepIndex = 0;  // The current step index
    this.shownSteps = [];   // All shown steps, so far 
    this.steps = $('.installerStep'); // Fetch all steps
    

    var that = this;
    //
    // Show the welcome screen
    //
    $('#welcomeScreen').fadeIn(300);
    $('#welcomeScreen .cintientLettering').fadeIn(300);
    setTimeout(
      function() {
        $('#welcomeScreen .greetings').fadeIn(1000);
      },
      1000
    );
    setTimeout(
      function() {
        $('#welcomeScreen .greetings').fadeOut(100);
        $('#welcomeScreen .cintientLettering').fadeOut(500);
        setTimeout(
          function(){
            $('#welcomeScreen').hide();
            $('#installer').fadeIn(500);
            that._showStep();
          },
          700
        );
      },
      4000
    ); 
  },
  
  
  _isStepCleared: function ()
  {
    return ($(".item.fail", $(this.steps[this.curStepIndex])).length === 0);
  },
  
  
  _nextStepListener : function (e)
  {
    e.preventDefault();
    e.stopPropagation();
    var self = e.data.self;
    var curStep = self.steps[self.curStepIndex];
    self.curStepIndex++; // Increment the step
    if (self.steps.length != self.curStepIndex) {
      $(curStep).fadeOut(100, function() {
        self._showStep();
      });
    } else {
      $('#installer').fadeOut(500)
      setTimeout(function() {
        $('#finished').fadeIn(300);
        self._submit();
      }, 500);
    }
  },
  
  
  _refreshProgressionButton: function()
  {
    var step = this.steps[this.curStepIndex];
    var button = $('input:submit', $(step));
    if (this._isStepCleared()) {
      if (this.steps.length == this.curStepIndex+1) {
        $(button).prop('value', "All set, let's go!");
      } else {
        $(button).prop('value', 'Next');
      }
      $(button).addClass('primary');
      $(button).removeClass('disabled');
      $(button).off('click');
      $(button).on('click', { self: this }, this._nextStepListener);
    } else {
      $(button).prop('value', 'Retry');
      // This is mandatory or else the click event gets registered for
      // the same button over and over again. Haven't tried the
      // stopImmediatePropagation() to try to prevent that though...
      $(button).off('click');
      $(button).on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('.inputCheckOnChange input', step).trigger('change');
      });
    }
    $(button).fadeIn(300);
  },
  
  
  _checkInput : function (key, input)
  {
    var remoteCheck = true;
    //
    // Account for the password special case (discard the passwordr check,
    // since by then the password check has already been performed).
    //
    if (key == 'passwordr') {
      key = 'password';
    }
    //
    // First we check to see if a local function exists for performing
    // a client-side check.
    //
    var functionName = 'inputCheckOnChange' + key.charAt(0).toUpperCase() + key.slice(1);
    if (typeof window[functionName] === 'function') {
      var result = eval(functionName + '()');
      this._updateInputValidation(input, result.ok, result.msg);
      remoteCheck = !result.ok;
      // Don't remote check email or passwords (on clean installs).
      if (key == 'email' || (key == 'password'/* && !this.upgrade*/))
      {
        return;
      }
    }
    //
    // Following is the remote - server-side - check.
    //
    if (remoteCheck) {
      var self = this;
      $.ajax({
        url: '?c=' + key + '&v=' + $(input).val(),
        type: 'GET',
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, XMLHttpRequest) {
          if (data == null || data.ok == null) {
            Cintient.alertUnknown();
          } else {
            self._updateInputValidation(input, data.ok, data.msg);
            return true;
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          Cintient.alertUnknown();
        }
      });
      return false;
    }
  },

  
  _showStep: function ()
  {
    var step = this.steps[this.curStepIndex];
    
    // Refresh the section title
    $(".page-header h1").html($(step).prop('title') + ' <small>step ' + (this.curStepIndex+1) + ' of ' + this.steps.length + '</small>');
    
    //
    // Remove fresh only items on upgrade 
    //
    if (this.upgrade) {
      $('.freshOnly', step).remove();
    } else {
      $('.upgradeOnly', step).remove();
    }
    
    var that = this;
    $('.inputCheckOnChange input', step).change(function() {
      that._checkInput($(this).prop('name'), $(this));
    });
    //
    // Create the next button
    //
    var button = document.createElement('input');
    $(button).prop('type', 'submit');
    $(button).addClass('btn');
    button.style.display = 'none';
    $("#actionNext", step).append(button); 
    
    // Make it visible
    $(step).fadeIn(150);
      
    this._refreshProgressionButton();
  },
  
  
  _submit: function ()
  {
    var data = function() {
      var x = {};
      /*
      $('input').each( function() {
        //x[this.name] = { type: this.type, value: this.value };
        x[this.name] = this.value;
      });
      $('textarea').each( function() {
        //x[this.name] = { type: this.type, value: this.value };
        x[this.name] = this.value;
      });*/
      // Following selector taken from cintient.js
      $(':input').filter(':not(:submit)').filter(':not(:button)').each( function() {
        x[this.name] = this.value;
      });
      return x;
    }();
    $.ajax({
      url: '?s=1',
      data: data,
      type: 'POST',
      cache: false,
      dataType: 'json',
      success: function(data, textStatus, XMLHttpRequest) {
        var result = 'Failed. Sorry about that...'
        if (data != null && data.ok != null && data.ok) {
          result = 'Finished!';
        } else {
          data.msg = '';
        }
        $('#finished').fadeOut(100, function () {
          $('#finished').html('<h1>' + result + '</h1><h4>' + data.msg + '</h4>');
          $('#finished').fadeIn(300);
        });
        return true;
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        Cintient.alertUnknown();
      }
    });
  },
  
  
  _updateInputValidation: function (input, ok, msg)
  {
    var parent = $(input).parents('.clearfix');
    //
    // Special case to tell us if this is an update or fresh install
    //
    if (ok == 2 && $(input).prop('name') == 'appWorkDir') {
      this.upgrade = true;
      ok = true;
    }
    if (ok == true) {
      $(parent).removeClass('fail');
      $(parent).addClass('success');
    } else {
      $(parent).removeClass('success');
      $(parent).addClass('fail');
    }
    $('~ .help-block', input).text(msg);
    this._refreshProgressionButton();
  },
};