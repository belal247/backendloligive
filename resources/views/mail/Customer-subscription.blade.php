<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $subject }}</title>
</head>
<body style="background-color: #ffffff; color: #000000; font-family: Arial, sans-serif; margin: 0; padding: 0;">
  <div style="padding: 20px;">
      <p>Dear {{ $mail_plan['customer_name'] }},</p>
    
    <p>Thank you for completing your subscription payment with <strong>CardNest LLC</strong>. Your account is now fully active, and you’re officially protected by our <strong>AI-powered fraud prevention technology</strong>.</p>

    <p>With your subscription, you now have access to:</p>

    <p>
        <ul>
            <li>
                <strong>Real-Time Fraud Detection</strong> – Every transaction scanned, analyzed, and validated instantly.
            </li>
            <li>
                <strong>Seamless Integration</strong> – API tools and developer support for quick onboarding.
            </li>
            <li>
                <strong>Enterprise-Grade Security</strong> – PCI-compliant, AES-encrypted data handling to ensure maximum protection.
            </li>
            <li>
                <strong>Scalable Protection</strong> – Whether you process hundreds or millions of transactions, CardNest adapts to your growth.
            </li>
        </ul>
    </p>

    <p>Your subscription details:</p>

     <p>
        <ul>
            <li>
                <strong>Plan:</strong> {{ $mail_plan['package_name'] }}
            </li>
            <li>
                <strong>Quantity of API Scans:</strong> {{ $mail_plan['monthly_limit'] }}
            </li>
            <li>
                <strong>Billing Amount:</strong> {{ $mail_plan['package_price'] }}
            </li>
            <li>
                <strong>Next Renewal Date:</strong> {{ $mail_plan['renewal_date'] }}
            </li>
        </ul>
    </p>

    <p>
        We deeply value your trust in CardNest. Our mission is to keep your business safe from online payment fraud so you can focus on what matters most—<strong>growing your revenue with confidence</strong>.
    </p>


     <p>
        We deeply value your trust in CardNest. Our mission is to keep your business safe from online payment fraud so you can focus on what matters most—<strong>growing your revenue with confidence</strong>.
    </p>

    <p>
        For assistance, you can always reach us at <strong>support@cardnest.io</strong> or through your client dashboard.
    </p>

    <p>
        Thank you once again for choosing CardNest. Together, we’ll stay one step ahead of fraudsters.
    </p>


     <img src="https://dw1u598x1c0uz.cloudfront.net/CardNest%20new-logo.gif"
          alt="CardNest Logo" 
             style=" width: 10% ; height: 10%; display: block;">

    <p>
        Warm regards,<br>
        <strong>The CardNest Team</strong><br>
        <a href="https://www.cardnest.io" target="_blank" style="color: #00aaff;">www.cardnest.io</a>
    </p>
  </div>

    <!-- Footer with background GIF -->
    <div style="padding: 20px; text-align: center; background: transparent;">
        <img src="https://d3rfyed8zhcsm.cloudfront.net/Adobe%20Express%20-%20fliped_video.gif" 
             alt="CardNest Animation" 
             style="width: 100%; height: auto; display: block;">
    </div>
</body>
</html>