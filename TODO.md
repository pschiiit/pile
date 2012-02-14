# TODO


## Rack port

Some parts of Rack are interesting to port to PHP.

### HTTP Spec conforming

#### Request

* `request.rb` : clean wrapper around HTTP request
* `methodoverride.rb` : allow use of HTTP methods non supported by browser throught a \_method param
* `head.rb` : force HEAD request to return empty body

#### Response headers

* `content_type.rb` : set a default Content-Type for response
* `content_length.rb` : set content length for response

#### Caching

* `etag.rb` : add ETag header to response
* `conditionnalget.rb` : return 304 response if possible

### Routing

* `urlmap.rb` : simple router to bind url to apps
* `cascade.rb` : return the first non-404 reponse from a bunch of apps
* `recursive.rb` : allow recursive apps calls

### Response handling

* `chunked.rb` : handle chunked response
* `sendfile.rb` : intercepts responses whose body is being served from a file and replaces it with a server specific X-Sendfile header

### Error handling

* `showstatus.rb` : explicit message for HTTP errors (or common status if asked)
* `showexception.rb` : pretty backtrace for exception. In PHP, this can prevent the "Uncateched exception" fatal error

### Monitoring

* `runtime.rb` : simple response time tracking
* `commonlogger.rb` : Log every request

### Other

* `lobster.rb` : find an easter egg

Some parts of Rack::Utils can be valuable :

* `build_query` and `build_nested_query`
* `status_code`
* Clean $_FILES mess to simplify upload managment. In PHP, this allow to handle case where uploaded file is larger than post_max_size, returning a 413 "Request entity too large" error.


## Pile specific development

Most of the logic in StdHandler can be separated into middlewares. 

### Middleware stack managment

* Add methods to insert / remove / swap middlewares in the stack

### HTTP Spec conforming

* Check Accept, Accept-Charset and Accept-language headers and return 406 error when needed

### Monitoring

* Define error handler and exit method to log errors to a file
* Add X-header to response to track memory usage

### Utility

* MagicQuotes cleaning
* session wrapper
* cookie wrapper

## Packaging

Pile can be packaged as a PHAR archive to ease its use by other developpers and make it available throught [Packagist](http://packagist.org/). 
