aspito
======

Simple "Assets Pipeline" for PHP. Including SASS (CSS) and Uglify using node.js. Minifies the files and outputs gzip files that are the ones served in production environment.

Introduction
============

After been workin several months in Ruby on Rails projects, I missed some of their pipeline features in my small personal projects, specially SASS and Uglity. Son this its my approach to have a JS / CSS pipeline in small PHP projects.

Abstract / How it works
=======================

To use ASPITO it's supossed that you develop in a localhost environment (local PHP server like XAMPP for instance), and you "deploy" to aproduction server (usually uploading your files by FTP).

The process its easy, each time you reload your webpage in development environment (localhost) aspito:

- Reads all the CSS / JS files
- Puts them toguether in a single file
- Compiles SASS if needed
- Minifies all the files
- Writes a single gzip file with the result
- And returns a non-compressed file to the browser (much easier to debug)

Once you upload your site to the server

- Using a simple .htaccess rule server allways return the gziped / minified file (you even don't need to upload the original JS / CSS files)
- In production environment (remote / public webserver) no files are compiled / minified. Aspito only performs the compression tasks in localhost.

Requirements
============

First of all you need to install node.js in your machine, and also the modules **node-sass** and **uglify** that are used to compile SASS and minify JS files.
<http://nodejs.org/>

Config
======

**Some important config data to get this working with your projects**

1. Aspito asumes that if the domain name has a **.dev** extension you are in development, so to work in your localhost its necesary that you name your domain with **.dev** extension.

For instance, if your domain name in production server is:

    http://carloscabo.com

In your localhost must be named:

    http://carloscabo.dev

2. Once you install node.js and node-sass / uglify you'll need to modify the **'_classes/sonata.aspito.class.php'** file to add the path to the node binaries in your machine.

3. You can touch several things in the sonata.aspito.class.php file (as default filenames, for instance), but if you do so remember to edit the .htaccess files you can fin in the root folder and also in /css and /js folders.

Disclaimer
==========

This is a personal tool in a very beta stage, please use it carefully. Collaborations / sugestions are welcome, but remember this is a tool for small / personal projects.
