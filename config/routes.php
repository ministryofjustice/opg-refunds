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
$app->post('/v1/auth', Auth\Action\AuthAction::class, 'auth');
$app->get('/ping', App\Action\PingAction::class, 'ping');

//  Authenticated routes
$prefix = '/v1/cases';
$app->get($prefix . '/cases', App\Action\CasesAction::class, 'cases');
$app->get($prefix . '/caseworker/{id:\d+}', App\Action\CaseworkerAction::class, 'caseworker');
$app->get($prefix . '/spreadsheet', App\Action\SpreadsheetAction::class, 'spreadsheet');

//  Developer routes
$app->get('/dev/view-case-queue', Dev\Action\ViewCaseQueueAction::class, 'dev.view-case-queue');
$app->get('/dev/migrate', Dev\Action\MigrateAction::class, 'dev.migrate');
