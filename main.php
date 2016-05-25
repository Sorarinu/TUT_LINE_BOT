<?php
    require_once 'simple_html_dom.php';
    require_once 'variable.php';
    require_once 'func.php';
    $variable = new variable();
    $func = new func();

    $undergraduateInfo = array('CS' => 'コンピュータサイエンス学部', 'MS' => 'メディア学部', 'BS' => '応用生物学部', 'ME' => '機械工学科', 'EE' => '電気電子工学科', 'AC' => '応用化学科');
    $value = json_decode(file_get_contents('content.txt'), true);

    $data = $func->getInfo();   //休講情報取得
    file_put_contents('content.txt', json_encode($data, JSON_UNESCAPED_UNICODE));

    //Debug Dump
    //var_dump($data);
    //var_dump($value);
    $data = $func->compArrayData($data, $value);

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
                    $func->post($func->makePostData($column, trim($user[0]), 0), $variable->channelId, $variable->channelSecret, $variable->mid);
                }
            } else    //指定学部
            {
                foreach ($data as $id => $column)
                {
                    if (strpos($column['undergraduate'], $undergraduateInfo[trim($user[1])]) !== false)
                    {
                        $func->post($func->makePostData($column, trim($user[0]), 0), $variable->channelId, $variable->channelSecret, $variable->mid);
                    }
                }
            }
            usleep(200000);
        }
    }
?>

