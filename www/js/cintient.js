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
 * This is Cintient's JS helper class.
 * 
 * @author Pedro Mata-Mouros <pedro.matamouros@gmail.com>
 */

var Cintient = function (options)
{
  this.init(options);
};

Cintient.prototype = {
  /**
   * 
   */
  init: function (options)
  {
  },

  createButton: function(text)
  {
    var button = document.createElement('button');
    //
    // Style it
    //
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
    $(button).html("<div>" + text + "</div>");
    return button;
  }
};