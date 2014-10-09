# FuzzyBit CMS Web Client (Beta)

## Introduction

The FuzzyBit CMS is a content management system for building scalable web resources. The CMS features a new binary layout engine which is the keystone to the architecture. The CMS is fully RESTful, built in the MVC design pattern, features an API and a unique and flexible approach to web development.

This is a beta version of the app, it will be open sourced, the license is yet to be applied and all rights are reserved.

## Dependencies

### PHP

This app has been developed on the following versions of PHP:

* 5.3.10, 5.3.29
* 5.3.26, 5.3.29 (cli)

## Instructions

This client-side app has two components and this is the README for the **private** component.

The **private** component is:

* client/
	* applications/
	* php5/
	* README.md
	* default.htaccess

As mentioned, this app has two components (**public** and **private**) and this component is **private**.

Install the **private** 'client/' folder in the parent (or higher) folder of the 'public_html/' folder. Make note of the path to the **private** client and specify it in the **public** client 'constants.php' file if you have not already done so.

Create a copy of 'default.htaccess' as '.htaccess' and modify it as necessary.

Create a copy of 'php5/default.global.xml' as 'php5/global.xml' then read the configuration instructions below.

To complete the installation, go to http://www.fuzzybit.com/registration.php to obtain a secret key and specify an application ID.

## Configuration

TO DO

## Encryption

### Public/Private Keys
