aspito
======

Simple "Assets Pipeline" for PHP. Including SASS (CSS) and Uglify using node.js. Minifies the files and outputs gzip files that are the ones served in production environment.

Introduction
============

After been workin several months in Ruby on Rails projects, I missed some of their pipeline features in my small personal projects, specially SASS and Uglity. So this its my humble approach to have a JS / CSS pipeline in small PHP projects.

Abstract / How it works
=======================

To use ASPITO it's supossed that you develop in a **localhost environment** (local PHP server like XAMPP for instance), and you "deploy" to a **production server** (usually uploading your files by FTP).

The process its easy, each time you reload your webpage in development environment (localhost) **aspito** will:

- Reads all the CSS / JS files
- Puts them toguether in a single file
- Compiles SASS if needed
- Minifies all the files
- Writes a single gzip file with the result
- And returns a non-compressed file to the browser (much easier to debug in development)

**Once you upload your site to the server**

- Using a simple **.htaccess rule** server allways returns the gziped / minified file (you even don't need to upload the original JS / CSS files).
- In production environment (remote / public webserver) no files are compiled / minified. Aspito only performs the compression tasks in localhost.

Requirements
============

First of all you need to install node.js in your machine, and also the modules **node-sass** and **uglify** that are used to compile SASS and minify JS files.
<http://nodejs.org/>

Config
======

**Some important config data to get this working with your projects**

- Aspito asumes that if the domain name has a **.dev** extension you are in development, so to work in your localhost its necesary that you name your domain with **.dev** extension.

For instance, if your domain name in production server is:

    http://carloscabo.com

In your localhost must be named:

    http://carloscabo.dev

- Once you install node.js and node-sass / uglify you'll need to modify the **"_classes/sonata.aspito.class.php"** file to add the path to the node binaries in your machine.

- You can touch several things in the **sonata.aspito.class.php** file (as default filenames, for instance), but **if you do so remember to edit the .htaccess files you can fin in the root folder and also in /css and /js folders.**

Debug / Flags
=============

I've added some debub parameters to try to identify some of the usual projects before uploading to production.

Following how to add the flags and what does each one of them (always **uppercased**):

     <!-- CSS Files -->
     <link rel="stylesheet" type="text/css" href="__aspito.php?FLAG">
     
     <!-- JS Files -->
     <script type="text/javascript" src="__aspito.php?FLAG"></script>
     
## GZ

After process all the files (minimify, compile, clean... etc) Returns the resulting production file instead the result stored in the _buffer_.

This helps you to test the resulting compiled files in development environment before upload them to the server.

## MIN

Same as previous one, but returning the **minimified file uncompressed** (not Gzipped).

## BULK
Returns the resulting file before minifiying or compressing, this must be the same as the _buffer_ output, but read from resulting file.

## PROD

Simulates that we are on production server, that is, ASPITO does **not perform any compressión / compilation tasks, only return a previously generated GZ file**.

Disclaimer
==========

This is a personal tool in a very beta stage, please use it carefully. Collaborations / sugestions are welcome, but remember this is a tool for small / personal projects.

Todo
====

- Add the options to compile files using a local node.js server instead of using comandline.

- Add some kind of JS / CSS tests in the sample files of the repository to be sure the SASS Compiling / JS Minifying is working fine.

Known issues
============

- Node-sass crashes compiling sass files if you have any CSS rule with old filter-opacity attribute (I still don't know why)

    filter: alpha(opacity=XX);
