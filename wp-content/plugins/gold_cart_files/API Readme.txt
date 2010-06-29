If your gold API key is not working there are a number of things to check before requesting support. Here are some of the common things to look out for.

1/ Your API key may be associated with another website? If so then you need to de-activate the API key from your test site so that it is free to use on your new site.

2/ Your server does not support cURL or fsockopen. Your server needs to allow these protocols so that your website can communicate with the WP e-Commerce API database. WordPress itself uses fsockopen to activate Akismet spam protection therefore if Akismet works then so should your WP e-Commerce API key.

If you don't know what this means check with your web host or server administrator.

3/ Your firewall is blocking outgoing connections. When activating your API key your website will try and reach out and connect to the WP e-Commerce API database. You might need to ensure that our IP address 209.20.70.163 is added to your firewall.

4/ Your Gold files are in the wrong place. You need to ensure that your file structure is correct, gold files need to be unpacked and uploaded into the wp-content/uploads/wpsc/upgrades/ folder like so:

wp-content/uploads/wpsc/upgrades/gold_cart_files/

5/ You have not uploaded all the files correctly. It is possible that an Auto update went wrong or perhaps when you were uploading your files they did not copy over properly. You should check to make sure the files sizes are correct.

------------

For More information check out our forums and documentation page. Also try to make sure you are always using the latest version of WP e-Commerce and the Gold Cart files.

Documentation: http://www.instinct.co.nz/e-commerce/documentation
Forums: http://www.instinct.co.nz/forums
