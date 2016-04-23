Coovachilli hotspot login UI.
==============================
This is my implementation of hotspot login page. I designed it to be separated. ~~The main login script doesn't emit any output, but acts as dispatcher that will redirect user to one of many page.~~ The main login script will include pages from separated file, including:

* Coovachilli login page, encrypting password with challenge using chilli_response.
* WISPr error.
* Login form.
* Success redirect page.
