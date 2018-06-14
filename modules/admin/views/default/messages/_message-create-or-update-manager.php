<?php
use app\models\User;

/**
 * @var User $user
 * @var string $pass
 */
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <style type="text/css">
        html {
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }

        @media only screen and (min-width: 600px) {
            .table600 {
                width: 600px !important;
            }
        }

        @media only screen and (max-device-width: 600px), only screen and (max-width: 600px) {
            table[class="table600"] {
                width: 100% !important;
            }
        }
    </style>
</head>

<body style="margin: 0;padding: 0;">

<table class="letterStyle" width="600px" cellpadding="0" cellspacing="0" border="0"
       style="max-width:600px !important; min-width:320px; margin: 0 auto !important; display: block;">
    <tr>
        <td align="center" bgcolor="#ffffff">

            <!--[if (gte mso 9)|(IE)]>
            <table width="600" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
            <![endif]-->

            <table width="600px" border="0" cellspacing="0" cellpadding="0" class="table600">
                <!--2row-->
                <tr>
                    <table width="600px" class="table600">
                        <tr>
                            <td width="15%">&nbsp;</td>
                            <td align="center" width="70%">
                                <span style="font-family:Arial;
                                      font-size: 24px;
                                      line-height: 24px;
                                      color: #0f1b5f;
                                      text-align: center;
                                      font-weight: bold;
                                      height: 50px;
                                      margin-top: 10px;
                                      margin-bottom: 10px;
                                     display: inline-block;">
                                    <?= Yii::$app->name; ?>
                                </span>
                            </td>
                            <td width="15%">&nbsp;</td>
                        </tr>
                    </table>
                </tr>
                <!--2row-->

                <tr>
                    <td align="center">
                        <table width="600px" class="table600">
                            <tr>
                                <td align="center" style="height: 50px;">
                                    <span style="
                                        text-align: center;
                                        font-family: Arial;
                                        font-size: 18px;
                                        line-height: 24px;
                                        font-weight: bold;
                                        margin-top: 10px;
                                        ">
                                        Уважаемый(ая) <?php echo "$user->surname $user->name $user->patronymic"; ?>!
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="text-align: center;
                                    font-family: Arial;
                                    font-size: 18px;
                                    line-height: 24px;
                                    text-align: justify;">
                                        На сайте <a
                                            href="<?php echo "http://" . $_SERVER['HTTP_HOST'] ?>"><?= $_SERVER['HTTP_HOST'] ?></a>
                                        был
                                        <?php if ($user->created_at == $user->updated_at) { ?>
                                        добавлен
                                        <?php } else { ?>
                                            отредактирован
                                        <?php } ?>

                                        пользователь с
                                        Вашими учетными данными:<br><br>

                                        E-mail (логин): <?= $user->username; ?><br>
                                        Пароль: <?= $pass ?><br>
                                        Создан: <?= date('d.m.Y H:i:s', $user->created_at); ?><br>

                                        <?php if ($user->created_at != $user->updated_at) { ?>
                                            Последнее редактирование: <?= date('d.m.Y H:i:s', $user->updated_at); ?><br>
                                        <?php } ?>

                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="text-align: center;
                                    font-family: Arial;
                                    font-size: 18px;
                                    line-height: 24px;
                                    margin-top: 10px;
                                    text-align: justify;">
                                        В случае возникновения каких-либо вопросов, пожалуйста, свяжитесь с нами по
                                        телефону: <?= Yii::$app->params['phoneOfCompany'] ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <table width="600px" class="table600">
                            <tr style="height: 70px;
                                color: #0f1b5f;
                                font-family: Arial;
                                font-size: 18px;
                                line-height: 24px;
                                font-style: italic;">
                                <td align="right">
                                    <div>С уважением,</div>
                                    <div>команда ЧОУ ДПО &#171;ИПАП&#187;</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>

            <!--[if (gte mso 9)|(IE)]>
            </td></tr>
            </table><![endif]-->

        </td>
    </tr>
</table>

</body>
</html>