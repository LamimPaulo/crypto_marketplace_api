<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!-- NAME: 1 COLUMN -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liquidex</title>
</head>

<body>
<div class="">
    <div style="margin:0">
        <div style="background-color:#eeeeee;color:#555555;font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;font-size:14px;height:100%!important;line-height:1.4em;margin:0;padding:0;width:100%!important">
            <center>
                <table style="background-color:#eeeeee;border-collapse:collapse;border-spacing:0;color:#555555;font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;font-size:14px;height:100%!important;line-height:1.4em;margin:0;padding:0;width:100%!important"
                       width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                    <tr>
                        <td style="background-color:#eeeeee;color:#555555;font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;font-size:14px;height:100%!important;line-height:1.4em;margin:0;padding:1em;text-align:auto;width:100%!important"
                            valign="top" align="center">
                            <table style="border-collapse:collapse;border-spacing:0" width="600" cellspacing="0"
                                   cellpadding="0" border="0">
                                <tbody>
                                <tr>
                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;text-align:left">
                                        <table style="border-collapse:collapse;border-spacing:0" width="100%"
                                               cellspacing="0" cellpadding="0" border="0">
                                            <tbody>
                                            <tr>
                                                <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;text-align:left"
                                                    width="120" height="60">

                                                    <img src="https://navi.inf.br/storage/api/clients/3/liquidex.png"
                                                         width="120"/>

                                                </td>
                                                <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;padding-left:.8em;text-align:right">
                                                    {{ trans('mail.mail_under_analysis.subject') }}
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;text-align:left">
                                        <div style="background-color:#ffffff;border:1px solid #e5e5e5">
                                            <table style="border-collapse:collapse;border-spacing:0"
                                                   width="100%" cellspacing="0" cellpadding="0" border="0">
                                                <tbody>
                                                <tr>
                                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;max-width:264px;min-width:264px;padding:24px 24px 24px 12px;text-align:left;vertical-align:top;color:#999">

                                                        {{ trans('mail.hello') }}
                                                        <strong style="color:#000000">
                                                            {{ $user->name ?? $user->username }},
                                                        </strong>
                                                        <br>
                                                        <br>

                                                        <p>
                                                           {!! trans('mail.mail_under_analysis.info', ['email' => $user['email']]) !!}
                                                        </p>


                                                    </td>
                                                </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;font-size:.4em;height:.4em;line-height:1em;text-align:left">
                                        &nbsp;
                                    </td>
                                </tr>

                                <tr>
                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;text-align:center"
                                        valign="top" align="center">
                                        <table style="border-collapse:collapse;border-spacing:0" width="100%"
                                               cellspacing="0" cellpadding="0" border="0">
                                            <tbody>
                                            <tr>
                                                <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;padding:24px;text-align:center"
                                                    valign="top" align="center">

                                                    <div style="color: #A5A5A5; font-size: 12px; margin-bottom: 20px; padding: 0px 50px;">
                                                       {{ trans('mail.auto_message') }}
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;text-align:center"
                                        valign="top" align="center">
                                        <table style="border-collapse:collapse;border-spacing:0" width="100%"
                                               cellspacing="0" cellpadding="0" border="0">
                                            <tbody>
                                            <tr>
                                                <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;padding:24px;text-align:center"
                                                    valign="top" align="center">
                                                    <center>
                                                        <div style="color: #A5A5A5; font-size: 10px; margin-bottom: 5px;">
                                                            Copyright {{date('Y')}} Liquidex. Direitos
                                                            Reservados.
                                                        </div>
                                                    </center>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </center>

        </div>

    </div>
</div>
</body>
</html>







