# cake-doc-translate-progress

A web application to visualize translation progress of [CakePHP/docs](https://github.com/cakephp/docs/) project.

## Technology
- Linux / Unix server
	- Since this application executes some `git` commands, it might not work on Windows. Use WSL or Docker on Windows.
- Apache HTTP Server (you can also use other HTTP servers, like Nginx)
- PHP 8.2 or newer
- CakePHP 5.x
- SQLite3

## Local Development
This repository provides [Dockerfile](./Dockerfile).

- To set up, run `docker build -t motooka/cake-doc-translate-progress .`
	- you should run this again if `Dockerfile` or some other files are modified.
- To run locally, run `docker run --detach --rm --name cake-doc-translate-progress -p 8000:80 -v $(pwd)/:/repository motooka/cake-doc-translate-progress`
- To stop running, run `docker stop cake-doc-translate-progress`
	- if you can't wait 10 seconds, you can also use `kill`
- To use shell, run `docker run --rm -it -v $(pwd)/:/repository motooka/cake-doc-translate-progress bash`
- To install package updates and run database(SQLite3) migration after set up, run `docker run --rm -it -v $(pwd)/:/repository motooka/cake-doc-translate-progress /composer-install.sh`
