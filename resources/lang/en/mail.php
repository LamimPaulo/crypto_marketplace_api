<?php

return [
    'deposit' => [
        'reject' => [
            'subject' => 'Deposit Confirmation',
            'main_message' => 'The following error occurred while verifying your deposit:',
            'message' => 'Please access the platform and perform the deposit process again, you will need to resend the voucher.',
        ]
    ],
    'documents' => [
        'reject' => [
            'subject' => 'Document Validation',
            'main_message' => 'The following error occurred while checking your documents:',
            'message' => 'Please go to the platform and complete the submit process again if necessary.',
        ]
    ],
    'draft' => [
        'reject' => [
            'subject' => 'Withdrawal Confirmation',
            'main_message' => 'The following error occurred while checking your withdrawal:',
            'message' => 'Please access the platform and carry out the withdrawal process again, if necessary.',
        ]
    ],
    'withdrawal' => [
        'reject' => [
            'subject' => 'Withdrawal Confirmation',
            'main_message' => 'Your Withdrawal request has been reversed:',
            'message' => 'Please go to the platform and request again if necessary.',
        ]
    ],
    'transaction' => [
        'reject' => [
            'subject' => 'Transaction Confirmation',
            'main_message' => 'The following error occurred while verifying your transaction:',
            'message' => 'Please go to the platform and perform the transaction again.',
        ]
    ],
    'mail_under_analysis' => [
        'subject' => 'Account Under Analysis',
        'info' => 'Your account is under review by our team. You will be notified at the end of the verification. <br><br> If you want to expedite the process, please contact our team.<br>',
    ],
    'mail_verify' => [
        'subject' => 'Registration Confirmation',
        'title' => 'Email Verification',
        'button' => 'Click to confirm your registration',
        'info' => 'Your registered email is: <strong> :email </strong>, it can also be used to login to your account. In addition, your <strong> phone number and username </strong> will also serve as your login method. <br> <br> Please click the button below to confirm your identity and gain access to the platform.',
        'info_2' => 'Registration generated in',
        'info_3' => 'You are receiving this email to ensure the registration request is genuine.'
    ],
    'notify_login' => [
        'subject' => 'New Login Done',
        'title' => 'Security Alert',
        'info' => 'The system has detected access to your account:',
        'access' => 'Access in',
        'source' => 'Source',
        'info_2' => 'You are receiving this email to make sure it was you.',
        'info_3' => '- Always verify that the email has even been sent through a valid Liquidex address. We will never send you an email asking for your password or any personal information.',
        'info_4' => '- Do not access suspicious sites. If you access your account through a public Wi-Fi network, we strongly recommend using a VPN.',
        'info_5' => 'This is an automated message, please do not reply. If you have not made this request or are not our customer, please contact Liquidex immediately.',
    ],
    'password_change' => [
        'subject' => 'Password Change Request',
        'title' => 'Change Password',
        'info' => 'You have requested a password change. Click the button below to continue, if you did not request this exchange, just ignore this email.',
        'button' => 'Change my password',
        'check' => 'You are receiving this email to ensure the request is genuine.'
    ],
    'hello' => 'Hello',
    'auto_message' => 'This is an automated message, please do not reply.',
    'rights' => 'All Rights Reserved.',
    'cancel_account' => [
        'subject' => 'Account Cancellation Request.',
        'main_message' => 'Your account has been successfully canceled. You do not need to reply to this message.',
        'message' => 'Cancellation was effected by your pin confirmation and security token on the date of receipt of this message.'
    ]

];