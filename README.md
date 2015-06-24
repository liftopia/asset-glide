# Liftopia Asset Manager/Endpoint

## Get started

Install dependencies via composer.

```
$ composer install
```

## Start Local Server

Start local PHP Web Server. You can view the app by visiting [http://localhost:3000](http://localhost:3000).

```
$ ./server
```

## Accessing Images

Place your source image, images you want to serve, in the data/source folder. Now you can access your image from
the following url: http://localhost:3000/img/yourImageName.jpg.

Please view the documentation for glide to see all available custom options: [http://glide.thephpleague.com/api/size/](http://glide.thephpleague.com/api/size/).

## TODO
- Update route to allow for custom handlers per status codes, instead of having to create GET route for status code.
- Add custom manipulator for creating legacy resize and fill.