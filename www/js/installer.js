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
 * Installer
 * 
 * Installation process helper
 * 
 * Using the javascript prototype development pattern
 * 
 * @version 1.0
 * @param options
 */

var Installer = function (options)
{
  this.init(options);
};

Installer.prototype = {
  /* init */
  init: function (options)
  {
    this.options = $.extend({
      curStepIndex : 0,
      steps        : {},
      elemsClasses : 
      {
        step       : 'installerStep',
        checkTypes : ['checkPreEmptive', 'checkOnChange', 'form'] 
        //error     : 'error' ,
        //mandatory : 'mandatory'
      }/*,
      elemsIds : 
      {
        step : 'step',
        form : 'install'
      }*/
    }, arguments[0] || {});

    this._steps           = $('.'+this.options.elemsClasses.step);
    //this._stepsBuilt      = [];
    //this._mandatoryCheck  = false;
    this._run();
    
  },
  
  
  /**
   * Provide proper styling for on-the-fly created buttons
   */
  _styleButton : function(button)
  {
    $(button).each( function() {
      $(this).hover(
      function() {
        $(this).css({
          "cursor" : "pointer",
          "border" : "2px solid rgb(255,40,0)",
          "box-shadow" : "0px 0px 20px rgb(255,40,0)",
          "-webkit-box-shadow" : "rgb(255,40,0) 0px 0px 20px",
          "-moz-box-shadow" : "rgb(255,40,0) 0px 0px 15px"
        });
      },
      function() {
        $(this).css({
          "cursor" : "default",
          "border" : "2px solid #999",
          "box-shadow" : "2px 2px 10px #111",
          "-webkit-box-shadow" : "#111 2px 2px 10px",
          "-moz-box-shadow" : "#111 2px 2px 10px"
        });
      });
    }
    );  
  },
  
  
  /**
   * Runs the installer handler. If it's the first time, a splash screen
   * is shown. If not, the proper installation step is displayed.
   */
  _run : function ()
  {
    var that = this;
    // Show the splash screen
    if (this.options.curStepIndex == 0) {
      $('#splashHeader h1').fadeIn(300);
      $('#splashHeader img').fadeIn(300);
      setTimeout(
        function() {
          $('#splashHeader .greetings').fadeIn(1000);
        },
        1000
      );
      setTimeout(
        function() {
          $('#splashHeader .greetings').fadeOut(100);
          $('#splashHeader h1').fadeOut(500);
          $('#splashHeader img').fadeOut(500);
          setTimeout(
            function(){
              $('#splashHeader').hide();
              $('#logo').show(200);
              $('#mainMenu').fadeIn(500);
              that._displayStep();
            },
            700
          );
        },
        4000
      );
    } else {
      $('#logo').show(200);
      $('#mainMenu').fadeIn(500);
      this._displayStep();
    }
  },
  
  /**
   * Checks whether a given step was previously built
   */
  isBuilt : function (index)
  {
    for (var i=0; i<this._stepsBuilt.length; i++) {
      if (this._stepsBuilt[i] === index) {
        return false;
      }
    }
    return true;
  },
  
  _displayStep : function ()
  {
    var step = this._steps[this.options.curStepIndex];
    
    // Register all inputs
    var that = this;
    $("li", step).each(function() {
      if ($(this).attr('class') == 'inputCheckOnChange') {
        that._registerCheckOnChangeInput($(this).attr('id'), $("input", this));
      }
    });
    
    // Empty out all previously drawn buttons
    $("#actionButtons").empty();
    
    // Create the back button
    if (this.options.curStepIndex > 0) {
      var backBtn = document.createElement('button');
      this._styleButton(backBtn);
      backBtn.innerHTML = '<div>&larr; Back</div>';
      backBtn.className = 'buttonText backButton';
      $(backBtn).bind('click', {self:this}, this._previousStepListener);
      $("#actionButtons").append(backBtn);
    }
    // Create the retry/next button
    var button = document.createElement('button');
    this._styleButton(button);
    var ok = true; // TODO: validate all inputs and mandatory fields 
    if (ok) {
      button.innerHTML  = '<div>Next &rarr;</div>';
      button.className = 'buttonText';
      $(button).bind('click', {self:this}, this._nextStepListener);
      $("#actionButtons").append(button);
    } else {
      button.innerHTML = '<div>Retry</div>';
      button.className = 'buttonText';
      $(button).click(function(e) {
        e.preventDefault();
        window.location.href = '?step='+(this.options.curStepIndex+1);
      });
      $("#actionButtons").append(button);
    }
    
    // Refresh the section title
    $("#mainMenu #sectionName").text($(".stepTitle", step).text());

    // Make it visible
    $(step).fadeIn(150);
  },
  
  
  _registerCheckOnChangeInput : function (key, input)
  {
    $(input).change(function () {
      $.ajax({
        url: 'index.php?c=' + key + '&v=' + $(input).val(),
        //data: { v: input.value },
        type: 'GET',
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, XMLHttpRequest) {
          if (typeof(data.ok) === "undefined") {
            // TODO: Error somewhere
            return false;
          } else {
            var result = $(input).parent().next('.result');
            if (data.ok === true) {
              $(result).removeClass('error');
              $(result).addClass('success');
            } else {
              $(result).removeClass('success');
              $(result).addClass('error');
            }
            $(result).text(data.msg);
            return true;
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          // TODO: Error
          alert(errorThrown);
        }
      });
      return false;
    });
  },
  
  
  /* goes back a step */
  _previousStepListener : function (e)
  {
    e.preventDefault();
    var self = e.data.self;
    var curStep = self._steps[self.options.curStepIndex];
    // Hide this and show previous
    $(curStep).fadeOut(100, function () {
      self.options.curStepIndex--;
      // Update the breadcrumbs
      $('#mainMenu #historyBack .step-' + (self.options.curStepIndex+2)).addClass('ghosted');
      $('#mainMenu #historyBack .step-' + (self.options.curStepIndex+1)).removeClass('ghosted');
      self._displayStep();
    });
  },
  
  
  /* on step change action */
  _nextStepListener : function (e)
  {
    e.preventDefault();
    var self = e.data.self;
    var curStep = self._steps[self.options.curStepIndex];
    // Hide this and show next
    $(curStep).fadeOut(100, function () {
      self.options.curStepIndex++;
      // Update the breadcrumbs
      $('#mainMenu #historyBack .step-' + self.options.curStepIndex).addClass('ghosted');
      $('#mainMenu #historyBack .step-' + (self.options.curStepIndex+1)).removeClass('ghosted');
      self._displayStep();
    });
  }
};
