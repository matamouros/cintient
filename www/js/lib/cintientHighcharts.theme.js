/**
 * Cintient theme for Highcharts JS
 * @author Pedro Mata-Mouros
 */

Highcharts.theme = {
  colors: ['rgb(124,196,0)', 'rgb(255,40,0)'],
  //colors: ['#46A546', '#C43C35'],
  chart: {
    //width: 360,
    //width: 590,
    //height: 260,
    width: 290,
    height: 260,
    /*backgroundColor: '#303030',*/
    /*backgroundColor: 'whiteSmoke',*/
    /*backgroundColor: {
      linearGradient: [0, 0, 0, 270],
      stops: [
        [0.16, '#fff'],
        [0.6, '#ddd'],
        [0.9, '#bbb']
      ]
    },*/
    backgroundColor: {
      linearGradient: [0, 0, 0, 260],
      stops: [
        [0.16, '#fff'],
        [0.9, '#eee']
      ]
    },
    borderWidth: 0,
    borderRadius: 0,
    borderColor: '#878787',
    plotBackgroundColor: null,
    plotShadow: false,
    plotBorderWidth: 0,
    className: 'highchart',
    spacingBottom: 10
  },
  credits: {
    enabled: false
  },
  title: {
    style: { 
      color: '#555',
      textShadow: 'none',
      font: '12pt Lucida Grande, Verdana, Arial, Helvetica, sans-serif'
    }
  },
  subtitle: {
    style: { 
      color: '#555',
      font: '9pt Lucida Grande, Lucida Sans Unicode, Verdana, Arial, Helvetica, sans-serif'
    }
  },
  xAxis: {
    gridLineWidth: 0,
    lineColor: '#303030',
    tickColor: '#303030',
    labels: {
      style: {
        color: '#000'
      }
    },
    title: {
      style: {
        color: '#000',
        font: '9pt Lucida Grande, Verdana, Arial, Helvetica, sans-serif'
      }        
    }
  },
  yAxis: {
    alternateGridColor: null,
    minorTickInterval: null,
    gridLineColor: 'rgba(48,48,48,.2)',
    lineWidth: 0,
    tickWidth: 0,
    labels: {
      style: {
        color: '#000'
      }
    },
    title: {
      style: {
        color: '#000',
        font: '9pt Lucida Grande, Verdana, Arial, Helvetica, sans-serif'
      }        
    }
  },
  legend: {
    itemStyle: {
      color: '#303030',
      textShadow: 'none',
      textDecoration: 'none'
    },
    itemHoverStyle: {
      color: 'rgb(255,60,0)',
      textShadow: '0px 0px 6px rgba(255,40,0,1)',
      textDecoration: 'none'
    },
    itemHiddenStyle: {
      color: '#999',
      textShadow: 'none',
      textDecoration: 'line-through'
    },
    floating: false,
    backgroundColor: {
      linearGradient: [0, 0, 0, 50],
      stops: [
        [0, 'rgba(96, 96, 96, .1)'],
        [1, 'rgba(16, 16, 16, .1)']
      ]
    },
  },
  labels: {
    style: {
      color: '#CCC'
    }
  },
  
  tooltip: {
    backgroundColor: {
      linearGradient: [0, 0, 0, 50],
      stops: [
        [0, 'rgba(96, 96, 96, .8)'],
        [1, 'rgba(16, 16, 16, .8)']
      ]
    },
    borderWidth: 0,
    style: {
      color: '#FFF',
      textShadow: '#000 1px 1px 1px'
    }
  },
  
  plotOptions: {
    line: {
      dataLabels: {
        color: '#CCC'
      },
      marker: {
        lineColor: 'rgb(255,40,0)'
      }
    },
    spline: {
      marker: {
        lineColor: 'rgb(255,40,0)'
      }
    },
    scatter: {
      marker: {
        lineColor: 'rgb(255,40,0)'
      }
    },
    candlestick: {
      lineColor: 'rgb(255,40,0)'
    }
  },

  toolbar: {
    itemStyle: {
      color: '#CCC'
    }
  },
  
  navigation: {
    buttonOptions: {
      backgroundColor: {
        linearGradient: [0, 0, 0, 20],
        stops: [
          [0.4, '#606060'],
          [0.6, '#333333']
        ]
      },
      borderColor: '#000000',
      symbolStroke: '#C0C0C0',
      hoverSymbolStroke: '#FFFFFF'
    }
  },
  
  exporting: {
    buttons: {
      exportButton: {
        symbolFill: '#55BE3B'
      },
      printButton: {
        symbolFill: '#7797BE'
      }
    }
  },
  
  // scroll charts
  rangeSelector: {
    buttonTheme: {
      fill: {
        linearGradient: [0, 0, 0, 20],
        stops: [
          [0.4, '#888'],
          [0.6, '#555']
        ]
      },
      stroke: '#000000',
      style: {
        color: '#CCC',
        fontWeight: 'bold'
      },
      states: {
        hover: {
          fill: {
            linearGradient: [0, 0, 0, 20],
            stops: [
              [0.4, '#BBB'],
              [0.6, '#888']
            ]
          },
          stroke: '#000000',
          style: {
            color: 'white'
          }
        },
        select: {
          fill: {
            linearGradient: [0, 0, 0, 20],
            stops: [
              [0.1, '#000'],
              [0.3, '#333']
            ]
          },
          stroke: '#000000',
          style: {
            color: 'yellow'
          }
        }
      }          
    },
    inputStyle: {
      backgroundColor: '#333',
      color: 'silver'
    },
    labelStyle: {
      color: 'silver'
    }
  },
  
  navigator: {
    handles: {
      backgroundColor: '#666',
      borderColor: '#AAA'
    },
    outlineColor: '#CCC',
    maskFill: 'rgba(16, 16, 16, 0.5)',
    series: {
      color: '#7798BF',
      lineColor: '#A6C7ED'
    }
  },
  
  scrollbar: {
    barBackgroundColor: {
        linearGradient: [0, 0, 0, 20],
        stops: [
          [0.4, '#888'],
          [0.6, '#555']
        ]
      },
    barBorderColor: '#CCC',
    buttonArrowColor: '#CCC',
    buttonBackgroundColor: {
        linearGradient: [0, 0, 0, 20],
        stops: [
          [0.4, '#888'],
          [0.6, '#555']
        ]
      },
    buttonBorderColor: '#CCC',
    rifleColor: '#FFF',
    trackBackgroundColor: {
      linearGradient: [0, 0, 0, 10],
      stops: [
        [0, '#000'],
        [1, '#333']
      ]
    },
    trackBorderColor: '#666'
  }
};

// Apply the theme
var highchartsOptions = Highcharts.setOptions(Highcharts.theme);
