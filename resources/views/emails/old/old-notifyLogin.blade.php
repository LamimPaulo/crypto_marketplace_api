<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!-- NAME: 1 COLUMN -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
</head>

<body>

<div style="background-color: #222533 !important; padding: 20px; font-family: font-size: 14px; line-height: 1.43; font-family: &quot;Helvetica Neue&quot;, &quot;Segoe UI&quot;, Helvetica, Arial, sans-serif; max-width: 100%; height: 100%">

    <div style="background-color: #222533 !important; max-width: 100%; height: 100%; padding: 20px;">

        <div style="background-color: #222533; width: 100%; height: 100%; padding: 20px;">

            <div style="max-width: 600px; margin: 0px auto; background-color: #fff; box-shadow: 0px 20px 50px rgba(0,0,0,0.05);">
                <table style="width: 100%;">
                    <tr>
                        <td style="background-color: #fff;">
                            <img src="https://s3.amazonaws.com/navi-public/api/navicapital.png"
                                 style="width: 150px; padding: 20px"/>
                        </td>
                        <td style="padding-left: 50px; text-align: right; padding-right: 20px;">
                            {{ trans('mail.notify_login.title') }}
                        </td>
                    </tr>
                </table>
                <div style="color: #000000; padding: 60px 70px; border-top: 1px solid rgba(0,0,0,0.05);">
                    <h1 style="margin-top: 0px;">
                        {{ trans('mail.hello') }} {{ $info->name ?? $info->username }},
                    </h1>
                    <div style="color: #636363; font-size: 14px;">
                        <p>
                            {{ trans('mail.notify_login.info') }}
                        </p>
                    </div>

                    <table style="margin-top: 10px; width: 100%;">
                        <tr>
                            <td style="max-width: 150px;">
                                <img src="https://s3.amazonaws.com/navi-public/api/lock.png"
                                     style="max-width: 150px; padding: 20px"/>
                            </td>
                            <td style="padding-right: 30px;">
                                <div
                                        style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px; font-weight: bold; color: #B8B8B8; margin-bottom: 5px;">
                                    #IP
                                </div>
                                <div style="font-size: 12px; color: #111; font-weight: bold; margin-bottom: 10px;">
                                    {{$info['ip']}}
                                </div>
                                <div
                                        style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px; font-weight: bold; color: #B8B8B8; margin-bottom: 5px;">
                                    {{ trans('mail.notify_login.access') }}
                                </div>
                                <div style="font-size: 12px; color: #111; font-weight: bold; margin-bottom: 10px;">
                                    {{$info['created']}}
                                </div>
                                <div
                                        style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px; font-weight: bold; color: #B8B8B8; margin-bottom: 5px;">
                                    {{ trans('mail.notify_login.source') }}
                                </div>
                                <div style="font-size: 12px; color: #111; font-weight: bold;">
                                    {{$info['agent']}}
                                </div>
                            </td>

                        </tr>
                    </table>

                    <h4 style="margin-bottom: 10px; color: #000000">
                        {{ trans('mail.notify_login.info_2') }}
                    </h4>
                    <div style="color: #636363; font-size: 14px;">
                        <p>
                            {{ trans('mail.notify_login.info_3') }}
                        </p>
                        <p>
                            {{ trans('mail.notify_login.info_4') }}
                        </p>
                    </div>


                </div>
                <div style="background-color: #F5F5F5; padding: 40px; text-align: center;">

                    <div style="color: #A5A5A5; font-size: 12px; margin-bottom: 20px; padding: 0px 50px;">
                        {{ trans('mail.notify_login.info_5') }}
                    </div>

                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.05);">

                        <div style="color: #A5A5A5; font-size: 10px;">
                            Liquidex {{date('Y')}}. {{ trans('mail.rights') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

