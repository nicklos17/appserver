<?php

return array(
//天气预报
'weatherData' => 'weather:',

//存放天气图片的服务器
'weatherServerUrl' => 'http://applocal.yunduo.com',

//天气气象对应图片url
'weatherPicUrl' => '/static/images/weather/2x/',

//天气预报相关
'weatherConfig' => array('url' =>'http://apis.haoservice.com/weather?cityname=%s&key=8187c963721f4b8791b25765fe3a5c50', 'key' => '8187c963721f4b8791b25765fe3a5c50'),
'pm25' => array('url' => 'http://apis.haoservice.com/air/cityair?city=%s&key=28b4745605834cc982b5093a067da0e9', 'appKey' => '28b4745605834cc982b5093a067da0e9'),

/**
 * 显示不同天气状况下的宝贝图片
 * 依据温度、下雨、和空气质量展示：
 * 温度划分： 严寒：sc 寒冷：cold 阴冷：dank 凉爽：cool 炎热：hot
 * 示例：严寒-下雨-中度以上污染： sc-rain-poll.png
 */
'babyPicForWeather' => array(1 => '/static/images/weather/baby/boy-%s-%s-%s.png', 3 => '/static/images/weather/baby/girl-%s-%s-%s.png'),

'unknown_weather' => '未知天气',

//中雨
'rain2' => array('08', 21),
//大雨
'rain3' => array('09', 22),
//暴雨
'rain4' => array(10, 11, 12, 23, 24, 25),
//中雪
'snow2' => array(15, 26),
//大雪
'snow3' => array(16, 27),
//暴雪
'snow4' => array(17, 28),
//沙尘暴
'sandStrom' => array(20, 29, 30, 31),

//天气预报数据缓存时间
'weatherTime' => 28800,

//天气预报数据短期缓存时间
'weatherShortTime' => 1800,

//中雨 8 = 8 21
//大雨 9 = 9 22
//暴雨10 = 10 11 12 23 24 25
//缺19-冻雨
//中雪 15 = 15 26
//大雪 16 = 16 27
//暴雪 17 = 17 29  
//沙尘暴 20 = 20 29 30 31

'weather' => array(
    '00' => '晴',
    '01' => '多云',
    '02' => '阴',
    '03' => '阵雨',	
    '04' => '雷阵雨',
    '05' => '雷雨伴有冰雹',
    '06' => '雨夹雪',
    '07' => '小雨',
    '08' => '中雨',
    '09' => '大雨',
    '10' => '暴雨',
    '11' => '大暴雨',
    '12' => '特大暴雨',
    '13' => '阵雪',
    '14' => '小雪',
    '15' => '中雪',
    '16' => '大雪',
    '17' => '暴雪',
    '18' => '雾',
    '19' => '冻雨',
    '20' => '沙尘暴',
    '21' => '小到中雨',
    '22' => '中到大雨',
    '23' => '大到暴雨',
    '24' => '暴雨到大暴雨',
    '25' => '大到特大暴雨',
    '26' => '小到中雪',
    '27' => '中到大雪',
    '28' => '大到暴雪',
    '29' => '浮尘', 
    '30' => '扬沙',
    '31' => '强沙尘暴',
    '53' => '霾'
    )
);