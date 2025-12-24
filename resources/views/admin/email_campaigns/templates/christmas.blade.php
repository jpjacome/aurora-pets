<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feliz Navidad — Aurora</title>
  <style>
    /* Email-safe basic styles */
    body { margin:0; padding:0; background: #fe8d2c; font-family: Arial, Helvetica, sans-serif; }
    table { border-collapse: collapse; }
    .email-container { width: 100%; max-width: 700px; margin: 0 auto; background: #00452A; }
    .hero { position: relative; width: 100%; height: 500px; overflow: hidden; }
    .hero img { display:block; width:100%; height:500px; object-fit:cover; }
    .hero-title { position: absolute; display: flex; flex-direction: column; left:0; right:0; top:50%; transform: translateY(-50%); text-align:center; color: #ffffff; }
    .hero-title .line { display:block; font-family: 'Playfair Display', serif; font-weight:700; font-size:56px; line-height:0.9; letter-spacing:2px; }
    .content { padding: 28px 32px; color: #dcffd6; font-size:16px; line-height:1.5; }
    .greeting { font-size:18px; margin: 0 0 12px 0; font-weight:600; }
    .paragraph { margin: 0 0 18px 0; }
    .logo { display:block; margin: 12px auto; width:120px; height:auto; }
    .footer-links { text-align:center; padding: 18px 32px 32px 32px; }
    .web-link { display:inline-block; margin-bottom:12px; color:#00452A; text-decoration:none; font-weight:700; }
    .social-links a { margin: 0 8px; color:#00452A; text-decoration:none; }
    @media screen and (max-width:480px){
      .hero-title .line { font-size:36px; }
      .hero img { height:320px; }
      .hero { height:320px; }
      .content{ padding:18px; }
    }
  </style>
</head>
<body>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fe8d2c; padding:20px 0;">
    <tr>
      <td align="center">
        <table role="presentation" class="email-container" cellpadding="0" cellspacing="0" width="700">
          <tr>
            <td style="padding:0;">
              <!-- Hero -->
              <div class="hero">
                <img src="/assets/imgs/email-templates/0_0.png" alt="Feliz Navidad" width="700" height="500" style="border:0; display:block;">
                <div class="hero-title">
                  <span class="line">FELIZ</span>
                  <span class="line">NAVIDAD</span>
                </div>
              </div>
            </td>
          </tr>

          <tr>
            <td class="content">
              <p class="greeting">Hola, {{ $user->name ?? 'amigo' }}</p>

              <p class="paragraph">Aurora te desea unas felices fiestas cerca de quienes te guardan el amor más puro.</p>

              <img src="/assets/home/imgs/logo4.png" alt="Aurora Logo" class="logo">

              <div style="text-align:center; margin-top:8px;">
                <a href="{{ url('/') }}" class="web-link">Visítanos en nuestra web</a>
              </div>
            </td>
          </tr>

          <tr>
            <td class="footer-links">
              <div class="social-links">
                <a href="https://www.facebook.com/" target="_blank" rel="noopener">Facebook</a>
                &nbsp;|&nbsp;
                <a href="https://www.instagram.com/" target="_blank" rel="noopener">Instagram</a>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
