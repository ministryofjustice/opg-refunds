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
$app->get($prefix . '/claim', App\Action\ClaimAction::class, 'claim');
$app->get($prefix . '/user[/{id:\d+}]', App\Action\UserAction::class, 'user');
$app->get($prefix . '/caseworker/claim[/{id:\d+}]', App\Action\CaseworkerClaimAction::class, 'caseworker.claim');
$app->get($prefix . '/spreadsheet', App\Action\SpreadsheetAction::class, 'spreadsheet');

//  Developer routes
$app->get('/dev/applications', Dev\Action\ApplicationsAction::class, 'dev.applications');
$app->get('/dev/view-claim-queue', Dev\Action\ViewClaimQueueAction::class, 'dev.view-claim-queue');
$app->get('/dev/migrate', Dev\Action\MigrateAction::class, 'dev.migrate');
