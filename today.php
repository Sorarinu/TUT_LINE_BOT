<?php
    /**
     * Created by PhpStorm.
     * User: Sorarinu
     * Date: 2016/05/18
     * Time: 11:34
     */
    require_once 'simple_html_dom.php';
    require_once 'variable.php';
    require_once 'func.php';
    $variable = new variable();
    $func = new func();

    $undergraduateInfo = array('CS' => 'コンピュータサイエンス学部', 'MS' => 'メディア学部', 'BS' => '応用生物学部', 'ME' => '機械工学科', 'EE' => '電気電子工学科', 'AC' => '応用化学科');
    $data = $func->getInfo();   //休講情報取得

    if (!empty($data))
    {
        $mids = file('mids');

        foreach ($mids as $row)
        {
            $user = explode(",", $row); //$user[0] = mid, $user[1] = 学部

            if (strpos(trim($user[1]), 'ALL') !== false)   //全学部
            {
                foreach ($data as $id => $column)
                {
                    $infoDate = $column['date'];
                    $infoDate = htmlspecialchars($infoDate);
                    $infoDate = str_replace("&amp;nbsp;", "", $infoDate);
                    $infoDate = htmlspecialchars_decode($infoDate);
                    $infoDate = explode(" ", $infoDate);
                    $infoDate = substr($infoDate[0], 0, 17);

                    if (strpos($infoDate, date('Y年m月d日')) !== false)
                    {
                        $func->post($func->makePostData($column, trim($user[0]), 1), $variable->channelId, $variable->channelSecret, $variable->mid);
                    }
                }
            } else    //指定学部
            {
                foreach ($data as $id => $column)
                {
                    $infoDate = $column['date'];
                    $infoDate = htmlspecialchars($infoDate);
                    $infoDate = str_replace("&amp;nbsp;", "", $infoDate);
                    $infoDate = htmlspecialchars_decode($infoDate);
                    $infoDate = explode(" ", $infoDate);
                    $infoDate = substr($infoDate[0], 0, 17);

                    if (strpos($column['undergraduate'], $undergraduateInfo[trim($user[1])]) !== false)
                    {
                        if (strpos($infoDate, date('Y年m月d日')) !== false)
                        {
                            $func->post($func->makePostData($column, trim($user[0]), 1), $variable->channelId, $variable->channelSecret, $variable->mid);
                        }
                    }
                }
            }
            usleep(200000);
        }
    }
?>