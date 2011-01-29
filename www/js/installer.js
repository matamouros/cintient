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
    this.options = jQuery.extend({
      index    : 0,
      steps    : {},
      elemsClasses : 
      {
        step      : 'installer_step',
        error     : 'error' ,
        mandatory : 'mandatory'
      },
      elemsIds : 
      {
        step : 'step',
        form : 'install'
      }
    }, arguments[0] || {});

    this._steps           = jQuery('.'+this.options.elemsClasses.step);
    this._stepsBuilt      = [];
    this._mandatoryCheck  = false;
    this.showStep(this.options.index);
  },
  /* keep watching for mandatory fields */
  mandatoryWatchGuard : function ()
  {
    var inputs    = jQuery('.'+this.options.elemsClasses.mandatory),
        i;
    this._mandatoryCheck  = false;    
    for (i=0; i<inputs.length; i++) {
      if (jQuery(inputs[i]).is(":visible")) {
        this._mandatoryCheck = true;
        continue;
      }
    }
  },
  /* Nice highlights for all buttons and input type submits. */
  highlightButton : function(button)
  {
    $(button).each( function() {
  	  $(this).hover(
	    function() {
	      $(this).css({
	        "cursor" : "pointer",
	        "border" : "2px solid rgb(255,40,0)",
	        "box-shadow" : "0px 0px 40px rgb(255,40,0)",
	        "-webkit-box-shadow" : "rgb(255,40,0) 0px 0px 40px",
	        "-moz-box-shadow" : "rgb(255,40,0) 0px 0px 30px"
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
  /* shows a step given its array offset */
  showStep : function (index)
  {
	  console.log(this._steps);
    var step   = this._steps[index];
    
    if (!this.notBuilt(index)) {
      var button = document.createElement('button'), 
          backBtn;
      this.highlightButton(button);
      /* show next only if this step has no errors */
      if (jQuery('#'+this.options.elemsIds.step + '-' + (index+1) + ' .' + this.options.elemsClasses.error).length == 0) {
        button.innerHTML  = 'Next &rarr;';
        button.className  = index;
        jQuery(button).bind('click', {self:this}, this.onStepChange);
        jQuery(step).append(button);
      } else {
        button.innerHTML  = 'Retry';
        button.className  = index;
        jQuery(button).click(function(e) {
          e.preventDefault();
          window.location.href = '?step='+(index+1);
        });
        jQuery(step).append(button);
      }
    
      if (index > 0) {
        backBtn           = document.createElement('button');
        this.highlightButton(backBtn);
        backBtn.innerHTML = '&larr; Back';
        backBtn.className = index;
        jQuery(backBtn).bind('click', {self:this}, this.stepBack);
        jQuery(step).append(backBtn);
      }
      
      this._stepsBuilt.push(index);  
    }
    jQuery(step).fadeIn(150);
    this.mandatoryWatchGuard();
  },
  /* goes back a step */
  stepBack : function (e)
  {
    e.preventDefault();
    var self     = e.data.self,
        index    = jQuery(this).attr('class'),
        curStep  = self._steps[index--];        
    jQuery(curStep).fadeOut(100, function () {
      self.showStep(index);
    });
    //
    // Update the breadcrumbs
    //
    console.log(index);
    $('#mainMenu #historyBack .step-' + (index+2)).addClass('ghosted');
    $('#mainMenu #historyBack .step-' + (index+1)).removeClass('ghosted');
  },
  /* tells if a step was allready built */
  notBuilt : function (index)
  {
    for (var i=0; i<this._stepsBuilt.length; i++) {
      if (this._stepsBuilt[i] === index) {
        return true;
      }
    }
    return false;
  },
  /* on step change action */
  onStepChange : function (e)
  {
    e.preventDefault();
    var self     = e.data.self,
        index    = jQuery(this).attr('class');
    if (self._mandatoryCheck) {
      var inputs  = jQuery('.'+self.options.elemsClasses.mandatory),
          error   = false;
      for (var i=0; i<inputs.length; i++) {
        if (inputs[i].value === '') {
          error = true;
        }
      }
      if (error) {
        alert('Make sure all required fields are filled.');
        return false;
      }
    }
    if (index < (self._steps.length-1)) {
      var curStep  = self._steps[index++];
      /* hide this and show next */
      jQuery(curStep).fadeOut(100, function () {
        self.showStep(index);
      });
      //
      // Update the breadcrumbs
      //
      $('#mainMenu #historyBack .step-' + index).addClass('ghosted');
      $('#mainMenu #historyBack .step-' + (index+1)).removeClass('ghosted');
    } else {
      jQuery('#'+self.options.elemsIds.form).submit();
    }
  }
};
