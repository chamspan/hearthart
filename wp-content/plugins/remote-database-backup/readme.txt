=== Remote Database Backup ===
Contributors: binnyva
Donate link: http://www.binnyva.com/
Tags: backup,database,db,admin
Requires at least: 2.5
Tested up to: 2.8
Stable tag: 1.00.1

Lets you create and download SQL dumps of your wordpress database for backup.

== Description ==

This plugin creates SQL dumps of your wordpress database. It is based on the WordPress Database Backup plugin(http://www.ilfilosofo.com/blog/wp-db-backup) - but it removes some of the security restrictions in the plugin to enable automated remote backups. You still need the admin user name and password to do a remote backup.

== Installation ==

1. Download the zipped file.
1. Extract and upload the folder and its contents to /wp-contents/plugins/ folder
1. Go to the Plugin management page of WordPress admin section and enable the Remote Database Backup plugin

== How to Use ==

One the plugin is enabled, you create a backup by going to Manage > DB Backup. You can download the backups to your system or you can leave it on the server.

== Frequently Asked Questions ==

= How will I restore a SQL Dump? =

The plugin don't have a restoring facility yet. You will have to restore it manually using the phpMyAdmin interface available from your control panel(cpanel, plesk, etc.)

= How is this plugin better than WordPress Database Backup? =

Actually, its not. Wordpress database backup plugin is a better tool as long as you choose to backup the database manually or by automatic server backups. But if you want to automatically create and download the backup dumps to your system, you want Remote Database Backup plugin. I have created a script that will do this automatically for you.

= What is the ideal frequency of backups? =

Depends on what kind of blog you have. If you post frequently and have a lot of commenting going on, you will need to backup more frequently than a slower site.

= Security considerations? =

A folder named "backup-(random string)" is created in the wp-content directory to store backups. The 'random string' part of the name is different for different systems. Nefarious types who have a decent idea of the name of the db server, and the database, may be able to remotely load the gz file by guessing the timestamp that forms the final part of the backup file name, or by guessing the entire string correctly.  Admittedly a long shot, and the reward is only an encrypted admin password, besides all the contents of your blog.

If you are the paranoid type, delete the backups from the folder asap following download - this will prevent others from getting hands on your database dump.

== Screenshots ==

1. Admin interface for backuping the database.

== ChangeLog ==

= 1.00.0 =
* Started

= 1.00.1 =
* Works with WP 2.8
* Uses wpframe
