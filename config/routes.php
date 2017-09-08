<?php
/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Action\HomePageAction::class, 'home');
 * $app->post('/album', App\Action\AlbumCreateAction::class, 'album.create');
 * $app->put('/album/:id', App\Action\AlbumUpdateAction::class, 'album.put');
 * $app->patch('/album/:id', App\Action\AlbumUpdateAction::class, 'album.patch');
 * $app->delete('/album/:id', App\Action\AlbumDeleteAction::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Action\ContactAction::class,
 *     Zend\Expressive\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */

//  Unauthenticated routes
$app->route('/sign-in', App\Action\SignInAction::class, ['GET', 'POST'], 'sign.in');
$app->get('/sign-out', App\Action\SignOutAction::class, 'sign.out');
$app->get('/reset-password', App\Action\PasswordRequestResetAction::class, 'password.request.reset');

//  Authenticated routes
$prefix = '/cases';
$app->get($prefix . '/set-password', App\Action\PasswordSetNewAction::class, 'password.set.new');

//  Special case - the home page route requires authentication
$app->get('/', [
    App\Middleware\Auth\AuthMiddleware::class,
    App\Action\HomePageAction::class,
], 'home');
