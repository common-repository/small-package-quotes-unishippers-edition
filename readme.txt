=== Small Package Quotes - Unishippers Edition ===
Contributors: enituretechnology
Tags: eniture, Unishippers,parcel rates, parcel quotes, shipping estimates
Requires at least: 6.4
Tested up to: 6.6.1
Stable tag: 2.4.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Real-time small package (parcel) shipping rates from Unishippers. Fifteen day free trial.

== Description ==

A more connected world means more opportunities. That’s why customers count on our diverse portfolio of transportation, e-commerce, and business solutions. Our air, ground and sea networks cover more than 220 countries and territories, linking more than 99 percent of the world’s GDP.

**Key Features**

* Includes negotiated shipping rates in the shopping cart and on the checkout page.
* Ability to control which Unishippers services to display
* Support for variable products.
* Define multiple warehouses and drop ship locations
* Option to include residential delivery surcharge
* Option to mark up shipping rates by a set dollar amount or by a percentage.

**Requirements**

* WooCommerce 6.4 or newer.
* A Unishippers customer number.
* A Unishippers issued UPS account number.
* Your username and password to Unishippers.
* A Unishippers issued Request Key.
* An API key from Eniture Technology.

== Installation ==

**Installation Overview**

Before installing this plugin you should have the following information handy:

* Your Unishippers account number.
* Your username and password to Unishippers.

If you need assistance obtaining any of the above information, contact your local Unishippers
or call the [Unishippers](http://unishippers.com) corporate headquarters at 1.800.Go.Unishippers® (800.463.3339)..

A more extensive and graphically illustrated set of instructions can be found on the *Documentation* tab at
[eniture.com](https://eniture.com/woocommerce-unishepper-small-package-plugin/).

**1. Install and activate the plugin**
In your WordPress dashboard, go to Plugins => Add New. Search for "eniture small package quotes", and click Install Now on Small Package Quotes - Unishippers Edition.
After the installation process completes, click the Activate Plugin link to activate the plugin.

**2. Get an API key from Eniture Technology**
Go to [Eniture Technology](https://eniture.com/woocommerce-unishepper-small-package-plugin/) and pick a
subscription package. When you complete the registration process you will receive an email containing your API key and
your login to eniture.com. Save your login information in a safe place. You will need it to access your customer dashboard
where you can manage your API keys and subscriptions. A credit card is not required for the free trial. If you opt for the free
trial you will need to login to your [Eniture Technology](http://eniture.com) dashboard before the trial period expires to purchase
a subscription. Without a paid subscription, the plugin will stop working once the trial period expires.

**3. Establish the connection**
Go to WooCommerce => Settings => Unishippers. Use the *Connection* link to create a connection to your Unishippers
account; and the *Setting* link to configure the plugin according to your preferences.

**4. Enable the plugin**
Go to WooCommerce => Settings => Shipping. Click on the link for Unishippers and enable the plugin.

== Frequently Asked Questions ==

= How do I get a Unishippers account number? =

Visit the customer support section of unishepper.com for FAQs, e-mail inquiries or telephone support. We also post verifications of insurance as a courtesy to our customers.

For additional information, call our customer service number: 1.800.Go.Unishippers® (800.463.3339).

= Where do I find my Unishippers username and password? =

Usernames and passwords to Unishippers.com.
Visit the customer support section of unishippers.com for FAQs, e-mail inquiries or telephone support. We also post verifications of insurance as a courtesy to our customers.

For additional information, call our customer service number: 1.800.Go.Unishippers® (800.463.3339).



= How do I get an API key from Eniture Technology key for my plugin? =

You must register your installation of the plugin, regardless of whether you are taking advantage of the trial period or 
purchased a API key outright. At the conclusion of the registration process an email will be sent to you that will include 
the API key. You can also login to eniture.com using the username and password you created during the registration process 
and retrieve the API key.

= How do I change my plugin Eniture Technology API key from the trail version to one of the paid subscriptions? =

Login to eniture.com. There you will be able to manage the API keys of all of your Eniture Technology plugins.

= How do I install the plugin on another website? =

The plugin has a single site API key. To use it on another website you will need to purchase an additional API key. If you want 
to change the website with which the plugin is registered, login to eniture.com. There you will 
be able to change the domain name that is associated with the API key.

= Do I have to purchase a second API for my staging or development site? =

No. Each API key allows you to identify one domain for your production environment and one domain for your staging or 
development environment. The rate estimates returned in the staging environment will have the word “Sandbox” appended to them.

= Why isn’t the plugin working on my other website? =

If you can successfully test your credentials from the Connection page (WooCommerce > Settings > Unishippers > Connections) 
then you have one or more of the following issue(s): 1) You are using the API key on more than one domain. 
The API key is for single site. You will need to purchase an additional API key. 2) Your trial period has expired. 
3) Your current API key has expired and we have been unable to process your form of payment to renew it. Login to eniture.com to resolve any of these issues.

= Why were the shipment charges I received on the invoice from Unishippers different than what was quoted by the plugin? =

Common reasons include one of the shipment parameters (weight, dimensions) is different, or additional services (such as residential 
delivery) were required. Compare the details of the invoice to the shipping settings on the products included in the shipment. 
Consider making changes as needed. Remember that the weight of the packing materials is included in the billable weight for the shipment. 
If you are unable to reconcile the differences call your local Unishippers office for assistance.

= Why do I sometimes get a message that a shipping rate estimate couldn’t be provided? =

There are several possibilities:

* Unishippers has restrictions on a shipment’s maximum weight, length and girth which your shipment may have exceeded.
* There wasn’t enough information about the weight or dimensions for the products in the shopping cart to retrieve a shipping rate estimate.
* The unishipper.com web service isn’t operational.
* Your Unishippers account has been suspended or cancelled.
* Your Eniture Technology API key for this plugin has expired.

== Screenshots ==

1. Plugin options page
2. Warehouses page
3. Quotes returned to cart

== Changelog ==

= 2.4.8 =
* Update:  Fixed Saturday delivery services quotes for new API

= 2.4.7 =
* Update: Introduced shipping rules.
* Update: Introduced backup rate feature.
* Update: Introduced error management feature.
* Fix: Corrected the tab navigation order in the plugin.
* Fix: Fixed the display of shipping rates on draft orders.

= 2.4.6 =
* Fix: Fixed conflict with LTL plugins.

= 2.4.5 =
* Fix: Fixed issues reported by WordPress team

= 2.4.4 =
* Update: Updated connection tab according to wordpress requirements 

= 2.4.3 =
* Update: Compatibility with WordPress version 6.5.1
* Update: Compatibility with PHP version 8.2.0
* Update: Introduced additional option to packaging method when standard boxes is not in use


= 2.4.2 =
* Update: Added compatibility with LTL plugin to suppress or allow parcel rates when the Less Than Truckload (LTL) threshold is met. 

= 2.4.1 =
* Fix: Fixed the position of the insurance field.

= 2.4.0 =
* Update: Display “Free Shipping” at checkout when handling fee in the quote settings is -100% .
* Update: Introduced the Shipping Logs feature.
* Update: Introduced “product level markup” and “origin level markup”.
* Update: Introduced insurance feature
* Update: Introduced signature required feature

= 2.3.5 =
* Fix: Fixed an issue with international services.

= 2.3.4 =
* Update: Compatibility with WooCommerce HPOS(High-Performance Order Storage)

= 2.3.2 =
* Update: Add programming to switch the Unishippers account to New/Old API. 

= 2.3.1 =
* Update: Added programming to automatically switch Unishippers account on new API.

= 2.3.0 =
* Update: Introduced Unishippers new API OAuth process with client ID and client secret.

= 2.2.1 =
* Update: Fixed grammatical mistakes in "Ground transit time restrictions" admin settings.

= 2.2.0 =
* Update: Introduced optimizing space utilization.
= 2.2.0 =
* Update: Introduced optimizing space utilization.
* Update: Modified expected delivery message at front-end from “Estimated number of days until delivery” to “Expected delivery by”.
* Fix: Inherent Flat Rate value of parent to variations.

= 2.1.8 =
* Update: Inherent product level flat rate value to it's variants.   

= 2.1.7 =
* Update: Text changes in FreightDesk.Online coupon expiry notice

= 2.1.6 =
* Update:  If ground Transit Time restrictions is enabled on quotes settings page then show it on product detail page. 

= 2.1.5 =
* Update:  Introduced a settings on product page to Exempt ground Transit Time restrictions.

= 2.1.4 =
* Update: Added compatibility with "Address Type Disclosure" in Residential address detection 

= 2.1.3 =
* Update: Compatibility with WordPress version 6.1
* Update: Compatibility with WooCommerce version 7.0.1

= 2.1.2 =
* Fix: Fixed conflict with micro-warehouse.

= 2.1.1 =
* Update: Introduced connectivity from the plugin to FreightDesk.Online using Company ID

= 2.1.0 =
* Fix: Fixed issue in plan change.

= 2.0.3 =
* Update: Compatibility with WordPress multisite network
* Fix: Fixed support link.

= 2.0.2 =
* Update: Compatibility with PHP version 8.1.
* Update: Compatibility with WordPress version 5.9.

= 2.0.1 =
* Fix: Corrected product page URL in connection settings tab

= 2.0.0 =
* Update: Compatibility with PHP version 8.0
* Update: Compatibility with WordPress version 5.8
* Fix: Corrected product page URL in connection settings tab

= 1.5.0 =
* Update: Added feature "Weight threshold limit".
* Update: Added feature In-store pickup with terminal information.

= 1.4.0 =
* Update: Added images URL for freightdesk.online portal.
* Update: CSV columns updated.
* Update: Virtual product details added in order meta data.
* Update: Compatibility with shippable addon.
* Update: Compatibility with micro-warehouse addon.

= 1.3.1 =
* Update: Introduced new features, Order detail widget for draft orders, improved order detail widget for Freightdesk.online, compatibly with Shippable add-on, compatibly with Account Details(ET) add-don(Capturing account number on checkout page).

= 1.3.0 =
* Update: Compatibility with WooCommerce 5.6

= 1.2.3 =
* Update: Compatibility with WooCommerce 5.5.

= 1.2.2 =
* Fix: Compatibility with Eniture Technology Freight plugins

= 1.2.1 =
* Update: Compatibility with WooCommerce 5.4.

= 1.2.0 =
* Update: Added features,  show delivery estimates, Box type, Box fee and multi-packaging feature.
 
= 1.1.1 =
* Update: Published on wordpress.org.

= 1.1.0 =
* Update: Compatibility with WooCommerce 4.9.

= 1.0.1 =
* Update: Compatibility with WooCommerce 4.8.
 
= 1.0.0 =
* Initial release.

== Upgrade Notice ==
