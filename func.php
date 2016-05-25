<?php
    /**
     * Created by PhpStorm.
     * User: Sorarinu
     * Date: 2016/04/18
     * Time: 22:01
     */
    require_once 'variable.php';

    class func
    {
        /**
         * Send to LINE.
         *
         * @param $postData
         * @param $channelId
         * @param $channelSecret
         * @param $mid
         *
         * @return mixed
         */
        public function post($postData, $channelId, $channelSecret, $mid)
        {
            $curl = curl_init("https://trialbot-api.line.me/v1/events");
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json; charset=UTF-8",
                "X-Line-ChannelID: $channelId",
                "X-Line-ChannelSecret: $channelSecret",
                "X-Line-Trusted-User-With-ACL: $mid"
            ));
            $result = curl_exec($curl);
            error_log($result);
            curl_close($curl);

            return $result;
        }

        /**
         * 取得したデータと保存されたデータを比較して更新分を返す
         *
         * @param $getValue
         * @param $fileValue
         *
         * @return mixed
         */
        public function compArrayData($getValue, $fileValue)
        {
            $getValue = json_decode(json_encode($getValue), true);
            $fileValue = json_decode(json_encode($fileValue), true);
            $value = $getValue;

            foreach($getValue as $gId => $gColumn)
            {
                foreach($fileValue as $fId => $fColumn)
                {
                    if(strpos($fColumn['date'], $gColumn['date']) !== false && strpos($fColumn['lecture'], $gColumn['lecture']) !== false)
                    {
                        unset($value[$gId]);
                        break;
                    }
                }
            }
            return $value;
        }

        /**
         * midsファイルから該当ユーザのmidを削除する
         *
         * @param $mid
         * @param $mids
         */
        public function deleteMid($mid, $mids)
        {
            if (isset($mid) === true && strpos($mids, trim($mid)) !== false)
            {
                $afterMids = file('./mids');
                for ($i = 0; $i < count($afterMids); $i++)
                {
                    if (strpos($afterMids[$i], trim($mid)) !== false)
                    {
                        $afterMids[$i] = "";
                    }
                }
                file_put_contents('mids', $afterMids);
            }
        }

        /**
         * 休講情報を取得する
         *
         * @return array
         */
        public function getInfo()
        {
            $cnt = array('date' => 0, 'lecture' => 0, 'teacher' => 3, 'undergraduate' => 5, 'grade' => 7, 'class' => 9, 'note' => 11, 'list' => 1);
            $data = array();
            while (true)
            {
                $html = file_get_html('http://www.teu.ac.jp/cc/cancel_hachi_pc_list.html/index.jsp?cancellist=' . $cnt['list']);
                if ($html === false)
                {
                    die();
                }
                if (is_null($html->find('tr th', $cnt['date'])->plaintext))
                    break;
                while (true)
                {
                    if (is_null($html->find('tr th', $cnt['date'])->plaintext))
                        break;
                    /**
                     * 講義データ
                     *
                     * date : 日付
                     * lecture : 講義名
                     * teacher : 担当教員
                     * undergraduate : 学部
                     * grade : 学年
                     * class : クラス
                     * note : 備考
                     */
                    $data[] = array(
                        'date' => trim($html->find('tr th', $cnt['date'])->plaintext),
                        'lecture' => trim($html->find('td b', $cnt['lecture'])->plaintext),
                        'teacher' => trim($html->find('td', $cnt['teacher'])->plaintext),
                        'undergraduate' => trim($html->find('td', $cnt['undergraduate'])->plaintext),
                        'grade' => trim($grade = $html->find('td', $cnt['grade'])->plaintext),
                        'class' => trim($html->find('td', $cnt['class'])->plaintext),
                        'note' => trim($html->find('td', $cnt['note'])->plaintext)
                    );
                    $cnt['date'] += 1;
                    $cnt['lecture'] += 1;
                    $cnt['teacher'] += 14;
                    $cnt['undergraduate'] += 14;
                    $cnt['grade'] += 14;
                    $cnt['class'] += 14;
                    $cnt['note'] += 14;
                }
                $cnt['date'] = 0;
                $cnt['lecture'] = 0;
                $cnt['teacher'] = 3;
                $cnt['undergraduate'] = 5;
                $cnt['grade'] = 7;
                $cnt['class'] = 9;
                $cnt['note'] = 11;
                $cnt['list'] += 10;
            }

            return $data;
        }

        /**
         * 送信するポストデータを成形する
         *
         * @param $column 内容
         * @param $to 送信先
         * @param $state cron=0, today=1, get=2
         *
         * @return array ポストデータ
         */
        function makePostData($column, $to, $state)
        {
            $variable = new variable();

            switch($state)
            {
                case 0:
                    $header = "**休講情報が更新されました**";
                    break;

                case 1:
                    $header = "**本日の休講情報**";
                    break;

                case 2:
                    $header = "**休講情報**";
                    break;

                default: break;
            }

            $text = "$header\r\n\r\n【日　時】$column[date]\r\n【講義名】$column[lecture]\r\n【教員名】$column[teacher]\r\n【学　部】$column[undergraduate]\r\n【学　年】$column[grade]\r\n【クラス】$column[class]\r\n【備　考】$column[note]";
            $text = htmlspecialchars($text);
            $text = str_replace("&amp;nbsp;", "", $text);
            $text = htmlspecialchars_decode($text);
            $response_format_text = ['contentType' => 1, "toType" => 1, "text" => $text];
            $postData = [
                'to' => [trim($to)],
                'toChannel' => $variable->toChannel,
                'eventType' => $variable->eventType,
                'content' => $response_format_text
            ];

            return $postData;
        }
    }

?>