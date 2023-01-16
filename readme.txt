=== AINSYS Integrations framework connector ===
Contributors: ainsys
Donate link: https://ainsys.com/
Tags: service integration, automation, data exchange
Requires at least: 5.5
Tested up to: 6.1
Stable tag: 4.3.6
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

**Building your business online? Create and update data pipelines and data exchange processes on the go with a new AINSYS Connector for WordPress.**

The AINSYS Connector plugin is part of an empowering no-code/low-code SaaS solution for system integration and two-way data exchange between WordPress and any other system. That includes ERPs, CRMs, CDPs, Google Sheets, WMS, DWH, and many other options.

One of AINSYS goals is to provide non-tech specialists with easy-to-use no-code tools so that they could start setting up most complex data exchange and automation scenarios within just minutes. You will need no JSON, SQL, or other tech skills, as well as help from any developers.

AINSYS offers a free solution for businesses that require small volumes of data processed — 5000 operations every month. Check out our pricing page to learn what other rates you can choose.

To successfully integrate the applications needed, the system requires to set up data exchange with the AINSYS Data Warehouse. This process will help to build and structure a single Truth Source properly. Data can be later on loaded into other systems. Thus, you will have no need in setting up and testing data exchange processes between WordPress and other systems to integrate several applications.

The process itself includes just a few steps:


To learn more about AINSYS and what connectors are currently available, visit AINSYS.com.


== Installation ==
= Connecting to the AINSYS system =
* Go to app.ainsys.com to register your project’s WorkSpace (2 min);
* Create a new WordPress connector in the Connectors page of your WorkSpace and receive a secret key for deploying (1 min);
* Setup your new AINSYS connector plugin and enter the secret key (1 min);
* To check whether or not the plugin functions properly, test the connection through the Master connection.

If WordPress or other plugins malfunction or have been modified, you may run into some problems while setting up a connector. Please use the testing and logging Wordpress and AINSYS mechanisms to identify the problem. We offer access to the open source code and documentation on REST API so that your team's developers could easily solve any issues.

If you have identified any AINSYS plugin issues, please, make sure to contact us through a form on our website. Our customer support will get back to you in less than 4 work hours. Make sure to fill out the form according to the Relevant Information instruction. This way, our specialists will be able to help you even faster.

= Setting up the exchange logic =
After the setup and testing processes are over, you will need to set the business logic to start syncing. To do that, you will need to specify 3 processes:

* Entities (users, orders, products) from which AINSYS will be downloading data;
* Where the data will be kept;
* Where to load new data.

If your WordPress system hasn’t been modified, you will be able to use standard templates; just follow the workflow setup instructions in your AINSYS WorkSpace. It will take you 2-3 minutes.

AINSYS connector can identify what entities you use, including:

* Standard WooCommerce fields and entities;
* Custom fields created with WooCommerce extensions;
* Forms and fields created with Contact Forms 7 plugin;
* Meta data.

AINSYS connector plugin allows admins to control precisely how data exchange logic is managed through the AINSYS interface. The following entity settings can be adjusted to the admin’s liking:

* Reading. Allows loading data from the WordPress field to AINSYS database. If this checkbox is empty, data will not be uploaded to AINSYS;
* Recording. Allows AINSYS to change the field’s content. If this checkbox is empty, data will be exchanged with AINSYS but not any other application;
* Data type - specifies what type of data is supposed to be transferred;
* Description. This field should include additional information on the logic.
* Data example. Other additional info for users.

= The plugin doesn’t read my website’s data structure correctly. What do I do? =
If data cannot be transferred from entities to the data structure table, AINSYS connector has not been able to automatically locate them. You can:

Use another method for creating a new field according to our instructions;
Modify the plugin’s source code;
Contact us. Our developers can help you solve any issues.

Once we identify and fix the problem, you will be able to continue working on the data structure again, setting up and testing out any business logic of your liking.


== FAQ ==

== Screenshots ==

== Changelog ==
