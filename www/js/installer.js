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
      steps    : {},
      elemsClasses : 
      {
        step : 'installer_step'
      },
      elemsIds : 
      {
        step : 'step',
        form : 'install'
      }
    }, arguments[0] || {});
    
    this._steps = jQuery('.'+this.options.elemsClasses.step);
    this.showStep(0);
  },
  /* shows a step given its array offset */
  showStep : function (index)
  {
    var step   = this._steps[index];
    var button = document.createElement('button');
    
    button.innerHTML = 'Continue &#8250;&#8250;';
    button.className = index;
    
    jQuery(button).bind('click', {self:this}, this.onStepChange);
    
    jQuery(step).append(button);
    jQuery(step).slideDown();
  },
  /* on step change action */
  onStepChange : function (e)
  {
    e.preventDefault();
    var self     = e.data.self;
    var index    = jQuery(this).attr('class');
    if (index < (self._steps.length-1)) {
      var curStep  = self._steps[index++];
      /* hide this and show next */
      jQuery(curStep).slideUp('slow', function () {
        self.showStep(index);
      });
    } else {
      jQuery('#'+self.options.elemsIds.form).submit();
    }
  }
};
