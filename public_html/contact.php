<?php
$sendMsg = '';

if (isset($_POST) && array_key_exists('mathCaptcha', $_POST)) { 
	if(!isset($_POST['name']) || empty($_POST['name'])){
		$sendMsg .= '<p style="color:red">Please check your name value.</p>';
	}
	if(!isset($_POST['email']) || empty($_POST['email'])){
		$sendMsg .= '<p style="color:red">Please check your email value.</p>';
	}
	if(!isset($_POST['message']) || empty($_POST['message'])){
		$sendMsg .= '<p style="color:red">Please check your message value.</p>';
	}
	
    if(!empty($sendMsg)) {
		$sendMsg = '<p style="color:red">We are sorry, but there appears to be a problem with the form you submitted.</p>'.$sendMsg;
    }
	else{
		$name = $_POST['name']; // required
		$email_from = $_POST['email']; // required
		$comments = $_POST['message']; // required

		//$email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
		//if(!preg_match($email_exp,$email_from)) {
		if (!filter_var($email_from, FILTER_VALIDATE_EMAIL)) {
			$sendMsg .= '<p style="color:red">The Email Address you entered does not appear to be valid.</p>';
		}

		$string_exp = "/^[A-Za-z .'-]+$/";
		$string_ex = "";

		if(strlen($name) < 2) {
			$sendMsg .= '<p style="color:red">The Name should be minimum 2 characters.</p>';
		}

		if(strlen($comments) < 10) {
			$sendMsg .= '<p style="color:red">The Description should be minimum 10 characters.</p>';
		}
	}
	
    function clean_string($string) {
        $bad = array("content-type","bcc:","to:","cc:","href");
        return str_replace($bad,"",$string);
    }
	
	if(empty($sendMsg)) {
		$email_message ='From: '.clean_string($name).'<br>'.
        'Email: '.clean_string($email_from).'<p>'.
        'Message:<br>'.clean_string($comments);

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: '.clean_string($name)." <".$email_from.">\r\n";

		if(mail("support@skitsbd.com", "support@skitsbd.com Contact Us Message From: ".$name, $email_message, $headers)){
			$sendMsg = "<p style=\"color:green\">We have received your question and will reply very soon. Thank you</p>";
		}
    }
}

$title= 'SK POS & Repair Tracking Pricing';  
$description='Affordable software for cell phone stores. Flat, fixed pricing. Unlimited users. Unlimited invoices. Unlimited repair tickets. Pay for a yesr and save 20%';
$keywords='';
include 'header.php';?>

<!-- Contact Us Section start here-->
<section class="dark-bg">
    <div class="columnMD12 flexColumn" style="color: #fff;">
        <h2>Contact Us</h2>
        <p>We’d love to hear from you.</p>
    </div>
</section>

<section class="contactUs">
    <div class="flexSpaBetRow">
        <div class="columnLG6 columnMD12" style="padding-right: 1rem;">
            <?php 
                echo $sendMsg;
			?>
            <form action="" method="post" onSubmit="return checkForm();" enctype="multipart/form-data" name="frmcontact" id="frmcontact">				
                <div class="flexSpaBetRow">
                    <div class="form-group columnMD6">
                        <label class="sr-only" for="cname">Your name</label>
                        <input type="text" class="form-control" id="cname" name="name" placeholder="Your name" minlength="2" required>
                    </div> 
                    <div class="form-group columnMD6">
                        <label class="sr-only" for="cemail">Email address</label>
                        <input type="email" class="form-control" id="cemail" name="email" placeholder="Your email address" required>
                    </div>
                </div>
                <div class="columnMD12">
                    <div class="form-group">
                        <label class="sr-only" for="cmessage">Your message</label>
                        <textarea class="form-control" id="cmessage" name="message" placeholder="Enter your message" minlength="10" rows="5" required></textarea>
                    </div>
                </div>
                <div>
                    <div class="columnMD12" style="margin-bottom: 1rem;">
                        <div id="mathCaptcha"></div>
                        <span style="color:red" id="reCaptcha"></span>
                    </div>

                </div>
                <div class="columnMD12" style="display: flex;">
                    <button type="submit" class="btn tryFreeBtn">Send Message</button>
                </div>
                <div id="form-messages"></div>
            </form>
        </div>
        <script type="text/javascript" src="./website_assets/js/mathCaptcha.js"></script>
        <script type="text/javascript">
            window.onload = function() {mathCaptcha();}
            function checkForm(){
                document.getElementById("reCaptcha").innerHTML = '';
                var response = checkMathCaptcha();
                    if(!response){
                    document.getElementById("reCaptcha").innerHTML = 'Wrong Captcha';
                    return false;
                }
                return true;
            }
        </script>

        <div class="columnLG6 columnMD12" style="text-align: left; padding-left: 1rem;">        
            <h3>Other ways to reach us</h3>
            <p style="margin-bottom: 1rem; margin-left: .5rem;">You can also get in touch with us:</p>
            <div class="columnMD12">
                <div class="contactUsFlex" style="align-items: center; margin-bottom: 1rem;">
                    <div style="margin-right: 1rem;">
                        <i class="fa fa-envelope contact-fa"></i> 
                    </div>
                    <div>
                        <a href="mailto:info@skitsbd.com">info@skitsbd.com</a>
                    </div>
                </div>
                <div class="contactUsFlex" style="align-items: center; margin-bottom: 1rem;">
                    <div style="margin-right: 1rem;">
                        <i class="fa fa-phone contact-fa"></i> 
                    </div>
                    <div>
                        <a href="tel:+6475561181">+6475561181</a><br>
                        <a href="tel:702-482-9233">702-482-9233 USA</a>
                    </div>
                </div>
                <div class="contactUsFlex" style="align-items: center; margin-bottom: 1rem;">
                    <div style="margin-right: 1rem;">
                        <i class="fa fa-map-marker contact-fa"></i> 
                    </div>
                    <div>
                        <p style="font-size: 1rem;">
                            <a target="_blank" href="http://maps.google.com/?q=66121 Town Center Pickering ON L1V3P8, Canada">PO Box 66121 Town Center Pickering<br> ON L1V3P8, Canada</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php';?>