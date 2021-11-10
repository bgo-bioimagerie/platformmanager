# Docker image for Platform-Manager

This image contains everything needed to create an instance of Platform-Manager
using an external mysql database

## Building

Specify to docker the build arg BRANCH (defaults to master)

   docker build --build-arg BRANCH=master ...

## Using the Container

We highly recommend using a `docker-compose.yml` to run your containers.

**UPDATE** docker-compose env variables to match your needs or create a .env file
based on env.example provided file.

If you use an external MySQL server, add MYSQL_xx env variables:

```yaml
version: "2"
services:
  report:
    image: quay.io/bgo_bioimagerie/platformmanager:latest
    environment:
        MYSQL_HOST: mysql # Host of the mysql server
        MYSQL_DBNAME: platform_manager # name of the database on the mysql server
        MYSQL_USER: platform_manager # Admin account to connect to mysql
        MYSQL_PASS: platform_manager # Password to connect to mysql
        SMTP_HOST: some.smtp.host # The hostname of an SMTP server to send emails
        MAIL_FROM: support@pfm.org # The sender address for emails sent by platformmanager (should be a real one to avoid being classified as spam)
        .....
    volumes:
      - ./data/platformmanager:/var/www/platformmanager/data/ # Mount the application data directory and backup it
    ports:
      - "3000:80"
```

See [./docker-compose.yml](docker-compose.yml) for another example for development purpose.
