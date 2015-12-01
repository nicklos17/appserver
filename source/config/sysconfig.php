<?php

return array(

    //token有效期
    'tokenTime' => 604800,

    //服务器url
    'url' => 'http://applocal.yunduo.com',

    //存放宝贝头像的服务器
    'babyPicServer' => 'http://applocal.yunduo.com',

    //存放用户图片的域名
    'userPicServer' => 'http://test.my.yunduo.com',

    //存放活跃度图片的域名
    'activePicServer' => 'http://applocal.yunduo.com',

    //消息图片存放路径
    'msgsPicServer' => 'http://appapi.yunduo.com:8020',

    //任务图片存放路径
    'tasksPicServer' => 'http://appapi.yunduo.com:8020',

    //baby图片的存放路径
    'babyPic' => '/static/images/baby',

    //用户图片的存放地址
    'userPic' => 'static/images/users',

    //消息图片的存放地址
    'msgPic' => '/static/images/msgs',

    //轨迹详情图片的存放路径
    'locatePic' => '/static/images/locate',

    //客户端错误日志存放地址
    'clientErrorLogs' => '/public/logs/clienterrors',

    //硬件更新信息路径
    'hardInfoUrl' => '/public/static/update/hardware',

    //续费套餐服务器
    'renewServer' => 'http://applocal.yunduo.com',

    //允许续费月数
    'allowRenew' => 3,
    //上传图片允许的最大尺寸
    'picMaxSize' => 100000000,

    //thrift服务的ip地址
    'thriftConf' => array('ip' => '118.192.91.15', 'port' => 6013, 'qqPort' => 6014),

    //swoole客户端的IP及端口配置
    'swooleConfig' => array('ip' => '192.168.0.194', 'port' => 9507),

    //验证码时效：1800秒
    'capTime' => '1800',

    //=================短信模板================
    //注册验证码
    'regCaptchaMsg' => '云朵儿童安全鞋:验证码：%s。感谢您注册成为云朵会员，该验证码30分钟内有效。',
    //找回密码验证码
    'resetCaptchaMsg' => '云朵儿童安全鞋:验证码：%s。您正在使用找回密码功能，如果不是您本人操作，请忽略，该验证码30分钟内有效。',
    //修改密码验证码
    'changeCaptchaMsg' => '云朵儿童安全鞋:验证码：%s。您正在使用修改密码功能，如果不做任何操作，系统将保留原密码。如果不是您本人操作，请及时联系客服。该验证码30分钟内有效。',
    //亲人添加验证码
    'addRelCaptchaMsg' => '云朵儿童安全鞋:验证码：%s。您正在请求成为宝贝%s的亲人号，请将该验证码告诉宝贝的监护号，该验证码30分钟内有效。',
    //=================短信模板================

    //规定日常消息列表的类型种类:1-到达，3-离开
    'dailylist' => array(1,3),
    //规定系统消息列表的类型种类:5-急需充电，7-续费提醒，9-异常提醒，11-低电，13-电量耗尽，15-满电，17-续费成功
    'systemlist' => array(5,7,9,11,13,15,17),

    //用户签到总天数
    'checkinTotal' => 'checkinTotal',
    //==========签到获得云币数量================
    //第一天
    'chenkinFirstDay' => '5',
    //连续2天
    'chenkinTwoDay' => '10',
    //连续3天
    'chenkinThreeDay' => '15',
    //连续4天
    'chenkinFourDay' => '20',
    //连续5天
    'chenkinFiveDay' => '25',
    //连续6天
    'chenkinSixDay' => '30',
    //==========签到获得云币================

    //=========================推送相关配置=============================//
    //***********************ios*******************************//
    //ios推送pem文件的路径
    'pemUrl' => dirname(dirname(__FILE__)) . '/apps/utils/push/apns/developCK.pem',
    //ios推送私钥
    'passphrase' => 'Y123456unduo',
    //ios推送的ssl地址
    'sslUrl' => 'ssl://gateway.sandbox.push.apple.com:2195',   //测试地址
    // 'sslUrl' => 'ssl://gateway.push.apple.com:2195',
    //***********************ios*******************************//
    //***********************android***************************//
    'jpushUrl' => './application/utils/push/android/JPushClient.php',
    'jpushAppKey' => '3affff6123d64761a7313887',
    'jpushSecret' => 'b29766d3950eff9cc3e9aa61',
    //***********************android***************************//

    //######################################推送文案################################
    //单条评论
    'commentPush' => '%s的%s评论%s %s的轨迹，赶紧去看看吧',
    //单条回复
    'replyPush' => '您收到一条新回复',
    //单条赞
    'praPush' => '%s的%s给%s %s的轨迹点了个赞!',
    //多条评论，回复和赞
    'mixPush' => '您有新的动态',
    //轨迹标注
    'markPush' => '%s的%s建议您给%s于%s的轨迹烙上成长的印迹哦',

    //显示在用户手机的系统消息
    'clientSystemMsg' => '您有新的消息',

    //用户在其他地方登录的文案
    'untokenMsg' => '您的帐号正在其他设备上登录，请重新登录。如非本人操作，请及时修改密码。',

    //用户添加亲人的文案
    'addfamilyMsg' => '您被设置为%s的亲人，您可以实时关注%s的位置动态。',
    //添加监护号推送
    'setGuaMsg' => '%s, 您已经被设置成我(%s)的监护号拉，记得经常关注我哦',
    //通知主号绑定童鞋
    'bindDevForHost' => '%s通知您帮%s绑定安全鞋哦，快点去绑定吧~',

    //续费推送
    'renewPushMsg' => array(
        //过期一天
        '0' =>  '安全鞋(ID：%s)服务已到期，今天开始它就无法定位了',
        //最后一天过期
        '1' => '安全鞋(ID：%s)服务截至今天，赶紧帮它续费吧',
        //还有5天就不能再续费
        '5' => '安全鞋(ID:%s)服务过期时间即将超过三个月，届时该安全鞋将无法重新续费',
        //还有8天,15天，30天过期
        '8' => '安全鞋(ID:%s)服务还剩%s天，记得帮它续费哦',
        //续费成功的消息，没有参与推送
        'success' => '%s在%s为安全鞋(ID:%s)成功续费，续费后安全鞋服务期延长至：%s'
        ),

    //######################################推送文案################################

    //ios端和android获得推送信息的key
    'pushKey' => 'content',

    //=========================redis相关配置===============================//
    //redis配置
    'redisConf' => array('server' => '192.168.0.20', 'port' => '49156', 'timeout' => '2.5'),
    //验证码队列名称
    'captchaKey' => 'captchaMsg',
    //童鞋工作模式选择的redis库
    'redisShoeModelBase' => '15',
    //童鞋工作模式redis的key
    'keyOfMode' => '%s:rate',
    //不同工作模式对应的值：1-省电 3-安全 5-休眠
    'devMode' => array('1' => '3,3', '3' => '1,1', '5' => '30,2'),
    //推送赞或者评论通知的key
    'pushForActive' => 'activeMsg',
    //推送用户被顶掉的key
    'untoken' => 'push:untoken',
    //推送添加亲人的key
    'addfamily' => 'push:addfamily',
    //轨迹赞和评论消息列表的key push:msg:list:用户id:宝贝id
    'tracksMsg' => 'push:msg:list:%s:%s',
    //日常消息和系统消息队列的key
    'dayAndSys' => 'push:daily:system',
    //获取日常消息和系统消息队列选择的redis库
    'redisPushBase' => '14',
    //签到状态数组名
    'checkinStatus' => 'checkin:status:%s',
    //签到key
    'checkin' => 'checkin:%s',
    //连续签到次数
    'signcount' => 'signCount:%s',
    //通过redis验证的任务组
    'redisCheckTaskGroup' => array('userEdit', 'bindQQ'),
    //设备和QQ绑定的推送
    'devBindQQ' => 'push:devBindQQ',
    //记录用户qq登录所用的accessToken
    'qqaccessToken' => 'accessToken:',
    //亲人添加记录未注册亲人
    'unregUser' => 'family:user:%s',
    //根据宝贝记录未注册的亲人
    'unregUserByBid' => 'family:baby:%s',
    //轨迹数据缓存
    'locusData' =>'locus:%s',
    //今日定位数据缓存
    'todayLocate' => 'locate:today:%s',

    //通过mysql验证的任务组
    'taskGroup' => array(
        'babyEdit' => 'babyEdit',
        'useDev' => 'useDev',
        'setFence' => 'setFence',
        'fenceCheckin' => 'fenceCheckin',
        'setGoal' => 'setStepGoal',
        'stepGoal' => 'stepGoal',
        'addFamily' => 'addFamily',
        'praise' => 'praise',
        'locusMark' =>   'locusMark',
        'comment' => 'comment'
    ),

    //任务进度计算
    'countProgress' => 'task:progress',
    //围栏签到
    'redisFenceCheckin' => 'fence:checkin:',
    //=========================redis相关配置===============================//
    //支付sdk配置
    'payment' =>
        array(
            'alipay' =>
                array(
                    'partner' => '2088311599513065',
                    'key' => '900ux70o498rbzw3keh6374nl49014qa',
                    'seller_id' => 'yunduo1@yunduo.com',
                     //支付完成后的回调处理页面
                    'notify_url' => '/paynotify/alipay'
                ),
            'wechat' =>
                array(
                    //财付通商户号
                    'partner' => '1220971201',
                    //财付通密钥
                    'partner_key' => '456c898c8532957571912bce809e28b3',
                    //appid
                    'app_id' => 'wxcceb8fd2d5b7f40a',
                    //appsecret
                    'app_secret'=> 'e41ce6afff244a17269d3ca1347ffb96',
                    //paysignkey(非appkey)
                    'app_key' => 'amXwZ5BWFOWHbA9zI8gzENSLgwz2A1fcPgxbTk1qiomD9zdTZwXriSEloTxDHmjROzLzGVe3GdHgNOEN3hzaKjEq2ZobHxOX0NSWFzDBstE8I4oGIa2f6Au67hINm6Fc',
                    //支付完成后的回调处理页面
                    'notify_url' => '/paynotify/wechat'
                )
        ),
    //续费推送
    'renewPushMsg' => array(
        //过期一天
        '0' =>  '安全鞋(ID：%s)服务已到期，今天开始它就无法定位了',
        //最后一天过期
        '1' => '安全鞋(ID：%s)服务截至今天，赶紧帮它续费吧',
        //还有5天就不能再续费
        '5' => '安全鞋(ID:%s)服务过期时间即将超过三个月，届时该安全鞋将无法重新续费',
        //还有8天,15天，30天过期
        '8' => '安全鞋(ID:%s)服务还剩%s天，记得帮它续费哦',
        //续费成功的消息，没有参与推送
        'success' => '%s在%s为安全鞋(ID:%s)成功续费，续费后安全鞋服务期延长至：%s'
        ),

    'renewDiscountPic' => array('8.9' => '/static/images/renews/icon_discount.png'),
    'renewPic' => '/static/images/renews/renew-%smonth.png',
    'payment' => array(
        'alipay' => array(
                            'partner' => '2088311599513065',
                            'key' => '900ux70o498rbzw3keh6374nl49014qa',
                            'seller_id' => 'yunduo1@yunduo.com',
                             //支付完成后的回调处理页面
                        'notify_url' => '/paynotify/alipay'
                            ),
        'wechat' => array(
                            //财付通商户号
                            'partner' => '1220971201',
                            //财付通密钥
                            'partner_key' => '456c898c8532957571912bce809e28b3',
                            //appid
                            'app_id' => 'wxcceb8fd2d5b7f40a',
                            //appsecret
                            'app_secret'=> 'e41ce6afff244a17269d3ca1347ffb96',
                            //paysignkey(非appkey)
                            'app_key' => 'amXwZ5BWFOWHbA9zI8gzENSLgwz2A1fcPgxbTk1qiomD9zdTZwXriSEloTxDHmjROzLzGVe3GdHgNOEN3hzaKjEq2ZobHxOX0NSWFzDBstE8I4oGIa2f6Au67hINm6Fc',
                            //支付完成后的回调处理页面
                            'notify_url' => '/paynotify/wechat'
                            )
    ),

    //消息列表标题
    'msgTitle' => array(
        '1' => '到达提醒',
        '3' => '离开提醒',
        '5' => '急需充电',
        '7' => '续费提醒',
        '9' => '异常提醒',
        '11' => '低电提醒',
        '13' => '关机提醒',
        '15' => '满电提醒',
        '17' => '续费成功提醒',
        '21' => '闲置提醒'
        ),

    //同年龄段宝贝平均步数： 6岁以下300步，7-9岁1000步，10-12岁3000步，15岁以上6000步
    'avgSteps' => array('6' => '3786', '9' => '6099', '12' => '8493', '15' => '9729', '16' => '10975'),

    //宝贝活跃程度:4以下不活跃，5-7正常，8以上活跃
    'active' => array('hardly' => array(0,1,2,3,4), 'normal' => array(5,6,7), 'most' => array(8,9,10)),

    //活跃度图片:1代表男生，3代表女生
'activePic' => array('1' => '/static/images/active/boy-%s.png', '3' => '/static/images/active/girl-%s.png'),

//根据活跃度提出建议
'activeAdvice' => array(
    'hardly' => '宝贝今天不是很活跃, 要多增加一些运动哦',
    'normal' => '宝贝今天状态还不错，要继续保持哦',
    'most' => '宝贝今天很活跃，给孩子多补充一些能量吧'
),

//一个宝贝最大的宝贝步数暂定为15000
'maxSteps' => 15000,

//计算轨迹圈的文件url
'locateCircleUrl' => '/static/locate.html',

//设备和qq绑定的文案
'qqBindDev' => array(
        'bind' => '您成功绑定云朵儿童安全鞋，qq将与您同步关注宝贝成长',
        'failAccesstoken' => '重新用qq登录app,即可实现qq关联设备的功能'
    ),

//根据云端返回的设备状态，编辑设备的状态信息
'msgForDevStatus' => array(
    '1' => '',
    '3' => '安全鞋所在位置网络信号较差,暂时无法获取安全鞋最新位置信息',
    '5' => '安全鞋已关机,无法获取安全鞋最新位置信息'
),

'qiniu' => array(
    'resourceUrl' => 'http://7xlw2c.com1.z0.glb.clouddn.com',  // 资源域名
    'accessKey' => 'CLwOWhI8T51VOI6PjBVpRwNtWoXE0haHQ3i7URyU',
    'secretKey' => 'H2ycclkL6Nan9X5amgTEj2mITLwi_7dXGqbSXrlm'
),
//客服电话
'service-phone' => '400-0000-400',
//检查验证码:redisKey-缓存的key;count-可验证次数;ttl-60秒
'checkCaptcha' => array('redisKey' => 'checkCap:%s', 'count' =>5, 'ttl' => 60)
);