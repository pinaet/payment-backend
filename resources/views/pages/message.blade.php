<!DOCTYPE html>
<html lang="en">
<!-- https://www.thanachartibiz.com/LeapCMSSignOnWebTbankPrd/leap/signon/pages/SignOnMFA.jsf -->
<head>
	<title>Payment - Harrow Asia Limited</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- fav icon -->
	<link rel="icon" type="image/png" href="{{url('images/icons/his-favicon.ico')}}"/>
	<!-- main style sheet -->
	<link rel="stylesheet" type="text/css" href="{{url('/css/main.css')}}">
</head>

<body>
    
	<div class="container-contact100">
		<div class="wrap-contact100">
			<span class="contact100-form-title">
				<img class="logo" src="{{url('/images/logo-his-bkk-blue.png')}}"><br>
				<!-- Payment Gateway -->
			</span>
			<p class="contact100-form-title">{{$title}}</p>
			
			<hr class='title-hr'>
			<div style='clear: both;'>
				<p class='aligncenter'>{{$message}}</p>
			</div>

			<p>&nbsp;</p>
			<p>&nbsp;</p>
			
			{{-- <div class="container-contact100-form-btn" id="login_btn_box">
				<div class="wrap-contact100-form-btn">
					<div class="contact100-form-bgbtn"></div>
					<button class="contact100-form-btn" tabindex="1" id="login_btn" onclick="location.href='{{$url}}';">
						<span>
							Go back
							<i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
						</span>
					</button>
				</div>
			</div> --}}
		</div>
	</div>

</body>

</html>