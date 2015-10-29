<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title>ECharts</title>
</head>
<body>
<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main" style="height:400px"></div>

<!-- ECharts单文件引入 -->
<script src="http://echarts.baidu.com/build/dist/echarts.js"></script>
<script type="text/javascript">
    // 路径配置
    require.config({
        paths: {
            echarts: 'http://echarts.baidu.com/build/dist'
        }
    });

    // 使用
    require(
        [
            'echarts',
            'echarts/chart/tree' // 使用柱状图就加载bar模块，按需加载
        ],
        function (ec) {
            // 基于准备好的dom，初始化echarts图表
            var myChart = ec.init(document.getElementById('main'));

            option = {
                title : {
                    text: '集群',
                    subtext: '集群拓扑机构图'
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : false,

                series : [
                    {
                        name:'树图',
                        type:'tree',
                        orient: 'vertical',  // vertical horizontal
                        rootLocation: {x: 'center',y: 50}, // 根节点位置  {x: 100, y: 'center'}
                        nodePadding: 50,
                        roam:true,
                        itemStyle: {
                            normal: {
                                label: {
                                    show: false,
                                    formatter: "{b}"
                                },
                                lineStyle: {
                                    color: '#48b',
                                    shadowColor: '#000',
                                    shadowBlur: 3,
                                    shadowOffsetX: 3,
                                    shadowOffsetY: 5,
                                    type: 'curve' // 'curve'|'broken'|'solid'|'dotted'|'dashed'

                                }
                            },
                            emphasis: {
                                label: {
                                    show: true
                                }
                            }
                        },

                        data: [
                            {
                                name: '根节点',
                                value: 6,
                                symbolSize: [60, 60],
                                symbol: 'image://http://localhost/custom/nginx.png',
                                children: [
                                    {
                                        name: '叶子节点1',
                                        value: 4,
                                        symbolSize: [60, 60],
                                        symbol: 'image://http://localhost/custom/tomcat.png',
                                    },
                                    {
                                        name: '叶子节点2',
                                        value: 4,
                                        symbolSize: [60, 60],
                                        symbol: 'image://http://localhost/custom/tomcat.png',
                                    },
                                    {
                                        name: '叶子节点2',
                                        value: 4,
                                        symbolSize: [60, 60],
                                        symbol: 'image://http://localhost/custom/tomcat.png',
                                    },
                                    {
                                        name: '叶子节点2',
                                        value: 4,
                                        symbolSize: [60, 60],
                                        symbol: 'image://http://localhost/custom/tomcat.png',
                                    }
                                ]
                            }
                        ]
                    }
                ]
            };

            var ecConfig = require('echarts/config');
            function eConsole(param) {
               var str = param.name + ":" + param.value;
                alert(str);
                //$("#divtest").css("top",event.clientY+$(this).scrollTop())
                //    .css("left",event.clientX+$(this).scrollLeft())
                //    .CSS("position","absolute")//
            }
            // 为echarts对象加载数据
            myChart.setOption(option);
            myChart.on(ecConfig.EVENT.CLICK, eConsole)
        }
    );
</script>
</body>