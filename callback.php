<?php
    require_once 'simple_html_dom.php';
    require_once 'variable.php';
    require_once 'func.php';

    $variable = new variable();
    $func = new func();
    $mids = file_get_contents('./mids');
    $content = file_get_contents('php://input');
    $headers = getallheaders();
    $undergraduate = array('ALL', 'CS', 'MS', 'BS', 'ME', 'EE', 'AC');
    $undergraduateInfo = array('CS' => 'コンピュータサイエンス学部', 'MS' => 'メディア学部', 'BS' => '応用生物学部', 'ME' => '機械工学科', 'EE' => '電気電子工学科', 'AC' => '応用化学科');
    $content = json_decode($content);
    $content = $content->result[0]->content;
    $opType = $content->opType; /* 友達追加orブロック */
    $receiveText = $content->text;  /* メッセージ受信時 (SET [ALL, CS,MS,BS,ME,EE,AC])*/
    $mid = (isset($receiveText)) ? $content->from : $content->params[0];

    /* 友達追加orブロック時 */
    if ($opType === 4)
    {
        if (isset($mid) === true && strpos($mids, $mid) === false)
        {
            $text = "東京工科大学 休講情報Botの友達登録ありがとうございます!\r\n\r\n" .
                "今後,休講情報が発表されましたら都度お知らせいたします.\r\n" .
                "尚,当Botが不要になった際には,LINEの友達から削除をしてください.\r\n\r\n" .
                "*お知らせ*\r\n" .
                "東京工科大学の休講情報がメールで通知されるサービスもございますので,是非ご活用ください→https://nxtg-t.net/kyukou/(現在停止中)\r\n\r\n" .
                "【機能について】\r\n" .
                "◎取得したい学部を指定し送信することで,その指定された学部の情報のみ受信することが可能です.\r\n\r\n" .
                "Format:SET [ALL,CS,MS,BS,ME,EE,AC]\r\n\r\n" .
                "例:SET CS\r\n\r\n" .
                "◎GETを送信することで,現時点で発表されている休講情報をその場で取得することができます.\r\n\r\n" .
                "Format:GET [ALL,CS,MS,BS,ME,EE,AC]\r\n\r\n" .
                "例:GET CS\r\n\r\n" .
                "ALL:全学部\r\n" .
                "CS:コンピュータサイエンス学部\r\n" .
                "MS:メディア学部\r\n" .
                "BS:応用生物学部\r\n" .
                "ME:工学部機械工学科\r\n" .
                "EE:工学部電気電子工学科\r\n" .
                "AC:工学部応用化学科";
            $response_format_text = ["contentType" => 1, "toType" => 1, "text" => $text];
            $postData = [
                'to' => [$mid],
                'toChannel' => $variable->toChannel,
                'eventType' => $variable->eventType,
                'content' => $response_format_text
            ];
            $func->post($postData, $variable->channelId, $variable->channelSecret, $variable->mid);
            file_put_contents('mids', $mid . ",ALL\r\n", FILE_APPEND | LOCK_EX);
        }
        else if(isset($mid) === true && strpos($mids, $mid) !== false)
        {
            $func->deleteMid($mid, $mids);

            $text = "東京工科大学 休講情報Botの友達登録ありがとうございます!\r\n\r\n" .
                "今後,休講情報が発表されましたら都度お知らせいたします.\r\n" .
                "尚,当Botが不要になった際には,LINEの友達から削除をしてください.\r\n\r\n" .
                "*お知らせ*\r\n" .
                "東京工科大学の休講情報がメールで通知されるサービスもございますので,是非ご活用ください→https://nxtg-t.net/kyukou/(現在停止中)\r\n\r\n" .
                "【機能について】\r\n" .
                "◎取得したい学部を指定し送信することで,その指定された学部の情報のみ受信することが可能です.\r\n\r\n" .
                "Format:SET [ALL,CS,MS,BS,ME,EE,AC]\r\n\r\n" .
                "例:SET CS\r\n\r\n" .
                "◎GETを送信することで,現時点で発表されている休講情報をその場で取得することができます.\r\n\r\n" .
                "Format:GET [ALL,CS,MS,BS,ME,EE,AC]\r\n\r\n" .
                "例:GET CS\r\n\r\n" .
                "ALL:全学部\r\n" .
                "CS:コンピュータサイエンス学部\r\n" .
                "MS:メディア学部\r\n" .
                "BS:応用生物学部\r\n" .
                "ME:工学部機械工学科\r\n" .
                "EE:工学部電気電子工学科\r\n" .
                "AC:工学部応用化学科";
            $response_format_text = ["contentType" => 1, "toType" => 1, "text" => $text];
            $postData = [
                'to' => [$mid],
                'toChannel' => $variable->toChannel,
                'eventType' => $variable->eventType,
                'content' => $response_format_text
            ];
            $func->post($postData, $variable->channelId, $variable->channelSecret, $variable->mid);
            file_put_contents('mids', $mid . ",ALL\r\n", FILE_APPEND | LOCK_EX);
        }
    } else if ($opType === 8)
    {
        $func->deleteMid($mid, $mids);
    }

    /* メッセージ取得時 */
    if (isset($receiveText))
    {
        $recieveData = explode(' ', trim($receiveText));
        if (isset($mid) === true && strpos($mids, $mid) !== false)
        {
            /* SET(学部登録) */
            if ($recieveData[0] === "SET")
            {
                for ($i = 0; $i < count($undergraduate); $i++)
                {
                    if ($recieveData[1] === $undergraduate[$i])
                    {
                        $func->deleteMid($mid, $mids);
                        $value = (strpos($recieveData[1], $undergraduate[0]) !== false) ? "全学部の休講情報をお知らせします." : $recieveData[1] . "学部の休講情報のみお知らせいたします.";
                        $text = "学部情報を登録しました.\r\n今後,$value";
                        $response_format_text = ["contentType" => 1, "toType" => 1, "text" => $text];
                        $postData = [
                            'to' => [$mid],
                            'toChannel' => $variable->toChannel,
                            'eventType' => $variable->eventType,
                            'content' => $response_format_text
                        ];
                        $func->post($postData, $variable->channelId, $variable->channelSecret, $variable->mid);
                        file_put_contents('mids', $mid . "," . $recieveData[1] . "\r\n", FILE_APPEND | LOCK_EX);
                        break;
                    }
                }
            } /* GET(全取得) */
            else if ($recieveData[0] === "GET")
            {
                for ($i = 0; $i < count($undergraduate); $i++)
                {
                    if ($recieveData[1] === $undergraduate[$i])
                    {
                        $data = $func->getInfo(); //休講情報取得
                        if (!empty($data))
                        {
                            if ($recieveData[1] === "ALL")   //全学部
                            {
                                foreach ($data as $id => $column)
                                {
                                    $func->post($func->makePostData($column, $mid, 2), $variable->channelId, $variable->channelSecret, $variable->mid);
                                }
                            } else    //指定学部
                            {
                                foreach ($data as $id => $column)
                                {
                                    if (strpos($column['undergraduate'], $undergraduateInfo[$recieveData[1]]) !== false)
                                    {
                                        $func->post($func->makePostData($column, $mid, 2), $variable->channelId, $variable->channelSecret, $variable->mid);
                                    }
                                }
                            }
                        }
                    }
                }
            } else if ($recieveData[0] === 'INFO' && isset($recieveData[1]) === false)
            {
                $mids = file('./mids');
                foreach ($mids as $userInfo)
                {
                    if (strpos($userInfo, $mid) !== false)
                    {
                        $data = explode(',', $userInfo);
                        $text = "登録されている学部 : " . trim($data[1]) . "学部\r\n".
                                "User Mid : " . trim($data[0]);
                        $response_format_text = ["contentType" => 1, "toType" => 1, "text" => $text];
                        $postData = [
                            'to' => [$mid],
                            'toChannel' => $variable->toChannel,
                            'eventType' => $variable->eventType,
                            'content' => $response_format_text
                        ];
                        $func->post($postData, $variable->channelId, $variable->channelSecret, $variable->mid);
                        break;
                    }
                }
            }
        }
    }
?>