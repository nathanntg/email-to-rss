email-to-rss
============

A very simple script designed for Heroku deployment. This script uses Heroku, S3 and Mailgun to
listen for incoming messages and append them to an RSS file. I created this for very selfish purposes,
I just wanted a way to keep the regular email newsletters that I enjoy and appreciate out of my inbox.

Right now, the system depends on a very clunky function in `configuration.i.php` to map different sender
email addresses onto different RSS files. A better solution is in order.

In order to deploy, you must have the following environment variables setup in Heroku:

* `MAILGUN_API_KEY` Your Mailgun private API key.
* `AWS_KEY` Your Amazon Web Services key.
* `AWS_SECRET` Your Amazon WebService secret.

Mailgun must be setup to route messages to the mailgun-end-point.php file. Routed emails will automatically
be appended to the RSS file and uploaded to the S3 bucket.

Known Issues
------------

This setup stores RSS files and HTML entries for each email on S3. Because of S3's eventual consistency,
there is the potential for race conditions where two emails arriving simultaneously could cause only
one to show up in the feed.
