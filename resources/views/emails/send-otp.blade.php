<!DOCTYPE html>
<html>

<head>
  <title>PRDP AUCTION ONE-TIME-PASSWORD</title>
</head>

<body>
  <div style="margin:50px auto;width:70%;padding:20px 0">
    <p style="font-size:1.1em">Hello!</p>
    <p>Welcome to PRDP PSO NL Auction. Use the following OTP to complete your Sign Up procedures. OTP is valid for 30 minutes</p>
    <h2 style="background: #00466a;margin: 0 auto;width: max-content;padding: 0 10px;color: #fff;border-radius: 4px;">{{$otp}}</h2>
    <p style="font-size:0.9em;">Regards,<br />PRDP PSO NL Auctioneer</p>
    <hr style="border:none;border-top:1px solid #eee" />
    <div style="float:right;padding:8px 0;color:#aaa;font-size:0.8em;line-height:1;font-weight:300">
      <p>© {{ date('Y') }} Philippine Rural Development Project</p>
    </div>
  </div>
  </div>
</body>

</html>