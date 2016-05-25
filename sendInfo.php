<?php
    /**
     * Created by PhpStorm.
     * User: Sorarinu
     * Date: 2016/04/27
     * Time: 20:13
     */
    require_once 'func.php';
    require_once 'variable.php';

    $func = new func();
    $variable = new variable();

    if (isset($_POST['submit']))
    {
        $mids = file('mids');

        foreach ($mids as $row)
        {
            $user = explode(",", $row);
            $text = $_POST['info'];
            $response_format_text = ["contentType" => 1, "toType" => 1, "text" => $text];
            $postData = [
                'to' => [trim($user[0])],
                'toChannel' => $variable->toChannel,
                'eventType' => $variable->eventType,
                'content' => $response_format_text
            ];

            $func->post($postData, $variable->channelId, $variable->channelSecret, $variable->mid);
        }
    }
?>

<html>
<head>
    <meta charset="utf-8"/>
    <title>TUT 休講情報BOT 情報配信</title>
</head>

<body>
<form action="<?php print($_SERVER['PHP_SELF']) ?>" method="post">
    <textarea id="info" name="info"></textarea>
    <input type="submit" id="submit" name="submit" value="送信">
</form>
</body>
</html>
