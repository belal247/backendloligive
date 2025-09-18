<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $subject }}</title>
 
  <style>
    body {
      background-color: #ffffff;
      color: #000000;
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    a {
      color: #00aaff;
    }

    footer {
      position: relative;
      width: 100%;
      overflow: hidden;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    footer video.bg-video {
      position: relative;
      width: 100%;
      height: auto;
      object-fit: cover;
      display: block;
    }

    /* Logo overlay */
    footer .logo-overlay {
      position: absolute;
      top: 10px;
      left: 20px;
      width: 10vw;  
    max-width: 150px; 
    min-width: 70px;  
    z-index: 2;
    }

    footer .logo-overlay video {
      width: 100%;
      height: auto;
      display: block;
    }
    .content{
        padding: 20px;
    }
  </style>

</head>
<body>
   <div class="content">
     <p>Dear CardNest Admin Team,</p>
    
    <p>This is an automated notification to inform you that <strong>new customer/business applications have been submitted and are pending review</strong>.</p>

    <p>Please log into the <strong>CardNest Admin Dashboard</strong> to review and take the necessary action.</p>

    <p><strong>Details of Pending Submissions</strong>:</p>

    <ul>
        <li>
            <strong>Total Applications Pending:</strong> {{ $admin['total_application_pending'] }}
        </li>
        <li>
            <strong>Submission Date(s):</strong> {{ $admin['submission_dates'] }}
        </li>
        <li>
            <strong>Customer/Business Name(s):</strong> {{ $admin['customer_business_names'] }}
        </li>
        <li>
            <strong>Status:</strong> Awaiting admin review and approval
        </li>
    </ul>

    <p><strong>Next Steps Required:</strong></p>

    <ol>
        <li>
            Verify all submitted information and required documentation.
        </li>
        <li>
            Conduct due diligence checks in line with <strong>CardNest compliance policies (KYC/AML requirements).</strong>
        </li>
        <li>
            Approve, reject, or request additional information from the applicant.
        </li>
        <li>
            Update the application status in the system accordingly.
        </li>
    </ol>

    <p>
        Please ensure reviews are completed within the <strong>standard 24-hour window</strong> to maintain our commitment to prompt onboarding for new customers and business partners.
    </p>

    <p>
        If you encounter any technical issues or discrepancies, kindly escalate to <strong>support@cardnest.io</strong> immediately.
    </p>

    <p>
        Thank you for your attention and commitment to maintaining the security and credibility of CardNest’s onboarding process.    
    </p>

    <p>
        Best regards,<br>
        <strong>CardNest System Notifications</strong><br>
        <a href="https://www.cardnest.io" target="_blank">www.cardnest.io</a>
    </p>
   </div>

    <!-- Footer with background video -->
      <footer>
    <!-- Background video -->
    <video class="bg-video" autoplay muted loop playsinline>
      <source src="https://d3rfyed8zhcsm.cloudfront.net/fliped_video.mp4" type="video/mp4">
    </video>

    <!-- Logo overlay video -->
    <div class="logo-overlay">
      <video autoplay muted loop playsinline>
        <source src="https://dw1u598x1c0uz.cloudfront.net/CardNest%20Logo%20WebM%20version.webm" type="video/webm">
      </video>
    </div>
  </footer>
</body>
</html>
