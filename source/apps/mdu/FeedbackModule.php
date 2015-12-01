<?php

namespace Appserver\Mdu\Modules;

class FeedbackModule extends ModuleBase
{

    const SUCCESS = '1';
    const FAILED_FEEDBACK = 10091;

    /**
     * ['用户反馈']
     * @param  [type] $content [反馈内容]
     * @param  [type] $uid     [用户id]
     * @param  [type] $uname   [用户名]
     * @param  [type] $version [客户端版本]
     * @param  [type] $os      [1-ios 3-android]
     * @return [type]          [description]
     */
    public function writeForApp($content, $uid, $uname, $version, $os)
    {
        $feedback = $this->initModel('\Appserver\Mdu\Models\FeedbackModel');
        if($feedback->add(
                $uid,
                $uname,
                $content,
                $version,
                $os,
                $_SERVER['REQUEST_TIME']) == 1)
        {
            return self::SUCCESS;
        }
        else
            return self::FAILED_FEEDBACK;
    }
}
