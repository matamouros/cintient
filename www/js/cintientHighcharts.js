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
 * This is Cintient's chart JS helper class.
 * 
 * @author Pedro Mata-Mouros <pedro.matamouros@gmail.com>
 */

var CintientHighcharts = function ()
{};

CintientHighcharts.prototype = {
  
  /**
   * 
   */
  unitTestChart : function (data)
  {
    var chart;
    chart = new Highcharts.Chart({
      chart: {
        width: 780,
        height: data.height,
        renderTo: data.renderTo,
        defaultSeriesType: 'bar',
        backgroundColor: data.backgroundColor
      },
      title: {
        text: data.title
      },
      xAxis: {
        categories: data.categories
      },
      yAxis: {
        min: 0,
        title: {
          text: 'Time (ms)'
        }
      },
      legend: {
        //reversed: true
      },
      /*tooltip: {
        formatter: function() {
          return '' + this.series.name + ': ' + this.y;
        }
      },*/
      plotOptions: {
        series: {
          stacking: 'normal'
        }
      },
      series: [{
        name: 'Ok',
        data: data.okData
      }, {
        name: 'Failed',
        data: data.failedData
      }]
    });
    return chart;
  }
};
