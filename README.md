# &#127826; CherryHttp
[![Build Status](https://travis-ci.org/kiler129/CherryHttp.svg)](https://travis-ci.org/kiler129/CherryHttp)
[![Code Climate](https://codeclimate.com/github/kiler129/CherryHttp/badges/gpa.svg)](https://codeclimate.com/github/kiler129/CherryHttp)  
Fast, secure, customizable and easy to use HTTP/1.1 server implementation. It's also licensed under MIT, so feel free to use it in your commercial project ;)  
Originally it was part of [TinyWs](https://github.com/kiler129/TinyWs), but it evolved into standalone project to provide modular architecture.

### What is supported and what's not?
Currently CherryHttp is somewhat limited due to lack of request body support, which means it cannot handle POST/PUT requests. It's subject to change in near future.  
For more information see FAQ note `Is it stable?`.

## Usage
### Requirements
  * PHP >=5.3 (5.6+ is recommended due to performance improvements)
  * [PSR-3 interfaces](https://github.com/php-fig/log)
  * [PSR-3 complaint logger](https://packagist.org/search/?tags=psr-3) is recommended (eg. lightweight [Shout](https://github.com/kiler129/shout))

### Installation
#### Using composer
Composer is preferred method of installation due to it's simplicity and automatic dependencies management.

  0. You need composer of course - [installation takes less than a minute](https://getcomposer.org/download/)
  1. Run `php composer.phar require noflash/cherryhttp` in your favourite terminal to install CherryHttp with dependencies
  2. Include `vendor/autoload.php` in your application source code
  3. If you want to see logs from server you need to install [PSR-3 complaint logger](https://packagist.org/search/?tags=psr-3), eg. `php composer.phar require noflash/shout` 
 
#### Manual
Details will be available soon.  
*Basically you need to download [PSR-3 interfaces](https://github.com/php-fig/log) and any [PSR-3 complaint logger](https://packagist.org/search/?tags=psr-3). Next put them in directory (eg. vendor) and include all files (or use PSR-4 complaint autoloader).*

### Basic usage
CherryHttp uses event-driven architecture. Basically you have to provide object implementing at least [`HttpRequestHandlerInterface`](https://github.com/kiler129/CherryHttp/blob/master/src/HttpRequestHandlerInterface.php), call `run()` on `Server` and that's it - server is running.  
Since one example is worth more than thousand words take a look at [all of them](https://github.com/kiler129/CherryHttp/blob/master/examples/), starting with [`HelloServer`](https://github.com/kiler129/CherryHttp/blob/master/examples/HelloServer.php).

## FAQ
#### Is it stable?
Yes, and no.  
This project can be considered as perfectly stable, production-ready implementation. However there are few important quirks (eg. missing POST support, converting headers names to lowercase) which need to be resolved before I can call this server production ready. Implementation of [PSR http-message](https://github.com/php-fig/fig-standards/blob/master/proposed/http-message.md) is also considered.
Basically saying you can use it in production environment with all of my projects, but be careful using it with your application outside development environment since some methods can behave differently soon.


#### How fast is it?
Well, not as fast as I wish, but doing 12Gb/s of throughput using single threaded PHP script sound impressive even for me ;)

Notes:
  * Disable X-Debug in production environment (it reduces performance ~10x). You need to comment out module in php.ini (setting xdebug.* constants to 0 is not enough).
  * Use *nix operating system - handling large amount of connections on Windows is not so efficient.

#### Does it work in HHVM environment?
Unfortunately I don't have professional expedience with this platform and cannot make any guaranties.

#### IPv6 support
IPv6 is fully supported, however it comes with few quirks. By default server binds to `0.0.0.0` address, which means it will be available via IPv4 only.  
You can use both IPv4 and IPv6 by passing `::` as first parameter to `Server::bind`, however there're two important drawbacks:
  * IPv4 clients ips will present in IPv6 form (eg. `127.0.0.1` becames `::ffff:127.0.0.1`)
  * On Windows XP and older binding to both IPv4 & IPv6 isn't possible on single port (it's OS limitation)

#### Could you add *PLACE FEATURE NAME HERE*?
Every feature request will be considered. Library is under active development, however I cannot implement everything right away myself, so pull-requests are kindly welcomed.

## Internal notes (for hackers)
#### How it works?
WIP

#### Multiple connections handling mechanism
Server use technique called synchronous I/O multiplexing utilizing [select(2)](http://linux.die.net/man/2/select) system call (or it's emulation on Windows platform). It doesn't require any extra libraries or additional compilation options.  
Server keeps track on every client socket and it's own server socket. After processing any information [stream_select()](http://php.net/stream_select) is called, which causes kernel to suspend whole PHP process (code freezes, no CPU is used during that time). When there's any expected activity on any socket, operating system wakes PHP process and applications continues. That process continues in endless loop.

#### Debugging core
Whole code contains a lot of debug messages wrapped into `$this->logger->debug()` calls. Due to performance reasons they're commented (unfortunately PHP doesn't offer builtin preprocessor like C does).  
There're also some additional verification code in some part of source, marked by `//TODO debug only` wrapped into multiline comment.

You can safely uncomment them all and see what's going on under the hood.

#### Why some methods uses type-hinting and some not?
Type hinting is a neat feature, which checks arguments types for programmer. Unfortunately every extra protection comes with extra code, resulting in performance degradation. How much does type-hint cost?  
![Performance chart](http://i.imgur.com/7fdDjFz.png)
Not so much in classic PHP application, a lot in high performance server. So, I made decision every method contains parameters types defined in PHP-Doc block, but only public & protected ones uses type-hinting.
