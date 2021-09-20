# PSGate - An Autonami Custom Connector
Autonami Plugin Custom Connector For local SMS gateway in Nigeria. Autonami is a wordpress plugin for marketing. It's use for broadcasting email/sms messages. You can automate your message with woocommerce, sign up, sign in, etc. on wordpress website. 

So with this custom connector called PSGate you can customize it to use local sms gateway in your country. all you need is the gatewar api or http call and their response, you account details to be integrated with it. For more information whatsapp me: +2347060624802

# How To Setup PSGate
1. Download Psgate folder
2. Copy it into your connectors folder in wp-marketing-automations-connectors folder
3. Open the Psgate folder
4. Please note: by default we integrate this with betasms.com in Nigeria. You can use your local sms gateway. This gateway (betasms) uses username and password to conenct to their gateway. Some sms gateways use only api_key while some use combination of apikey, phone number, and account id. e.g. twillio
5.  Open class-wfco-psgate-send-sms.php file inside calls folder and change the $this->api_end_point to your gateway url. You can check the process() function on this page to see if the http request matches yours.
6.  Also, open class-bwfan-psgate-send-sms.php in autonami\actions folder and edit the $this->api_end_point
7.  That is all, save and close the files.
8.  Login to your wordpress file and start using your connector.
9.  If you need technical support at affordable price, you can contact us to set it up for you. Thanks

# About The Author
Oyeyemi Olatunde Francis<br>
Full-Stack Developer
