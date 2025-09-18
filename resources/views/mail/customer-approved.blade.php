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
    .content {
      padding: 20px;
    }
  </style>

</head>
<body>
    <div class="content">
        <p>Dear {{ $accountHolderFirstName }},</p>
    
    <p>Congratulations! Your <strong>CardNest application is approved</strong> 🎉</p>

      <p>You are now part of an innovative network of businesses using <strong>AI and machine learning–driven fraud prevention technology</strong> to safeguard online transactions, reduce chargebacks, and ensure secure payments for their customers.</p>

    <p>Here’s what happens next:</p>

      <p>
        <ol>
            <li>
                <strong>Integration Access:</strong> You will receive your API keys and technical documentation to connect seamlessly with CardNest.
            </li>
            <li>
                <strong>Onboarding Support:</strong> Our customer success team will reach out with step-by-step guidance to ensure smooth integration.
            </li>
            <li>
                <strong>Continuous Protection:</strong> Once integrated, our system immediately begins scanning, detecting, and preventing fraudulent card transactions in real-time.
            </li>
        </ol>
    </p>


   <p>
        At CardNest, your trust and security come first. We never store sensitive cardholder data—everything is processed with <strong>real-time encryption (AES protocols, PCI-compliant)</strong> for maximum protection.
    </p>

    <p>
        If you need assistance at any point, our support team is available at <strong>support@cardnest.io</strong> or through your client dashboard.
    </p>

    <p>
        Thank you for trusting CardNest to protect your business. We look forward to helping you grow safely and confidently.
    </p>

    <p>
        Warm regards,<br>
        <strong>The CardNest Team</strong><br>
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